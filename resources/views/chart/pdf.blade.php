<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Eye Drop Chart</title>
<style>
body {
    font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
    font-size: 12px;
    margin: 10px;
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    page-break-inside: auto;
    margin-bottom: 20px;
}

thead {
    display: table-header-group; /* repeat headers */
}

tr {
    page-break-inside: avoid;
    page-break-after: auto;
}

th, td {
    border: 1px solid #444;
    text-align: center;
    padding: 3px;
}

th {
    background: #efefef;
}

.med-name {
    text-align: left;
    font-weight: bold;
    padding-left: 4px;
    width: 150px;
    white-space: nowrap;
}

.checkbox-cell {
    font-size: 20px;
    padding: 2px 0;
    width: 50px;
}

/* Each 2-week segment = page */
.chart-page {
    page-break-after: always;
}
.chart-page:last-child {
    page-break-after: avoid;
}
</style>
</head>
<body>
<h2 style="text-align:center;">Eye Drop Chart</h2>

@php
    $daysPerPage = 14;
    $totalDays = count($days);
@endphp

@for($offset = 0; $offset < $totalDays; $offset += $daysPerPage)
    @php
        // Handle either array or collection input safely
        $dayArray = is_array($days) ? $days : $days->all();
        $pageDays = array_slice($dayArray, $offset, $daysPerPage);

        // Convert all items to Carbon instances (if not already)
        $pageDays = array_map(fn($d) => $d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d), $pageDays);
        
        // Check if any medication is active on this page before rendering
        $pageStartDate = $pageDays[0] ?? null;
        $hasActiveMeds = false;
        
        foreach($meds as $med) {
            $totalWeeksForMed = count($med['weeks_schedule']);
            $medStart = \Carbon\Carbon::parse($med['start_date']);
            $medEndDate = $medStart->copy()->addWeeks($totalWeeksForMed);
            
            if ($pageStartDate && $medEndDate->greaterThanOrEqualTo($pageStartDate)) {
                $hasActiveMeds = true;
                break;
            }
        }
        
        // Skip this page if no medications are active
        if (!$hasActiveMeds) {
            continue;
        }
    @endphp


    <div class="chart-page">
        <table>
            <thead>
                <tr>
                    <th>Medication</th>
                    <th>Time</th>
                    @foreach($pageDays as $day)
                        <th>{{ $day->format('D m/d') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
@foreach($meds as $med)
    @php
        $weeks = $med['weeks_schedule'];
        $start = \Carbon\Carbon::parse($med['start_date']);
        
        // Check if this medication is active on this page
        $totalWeeksForMed = count($weeks);
        $medEndDate = $start->copy()->addWeeks($totalWeeksForMed);
        $pageStartDate = $pageDays[0] ?? null;
        
        // Skip this medication if it has ended before this page starts
        if ($pageStartDate && $medEndDate->lessThan($pageStartDate)) {
            continue;
        }
        
        // Determine which doses are needed for days on THIS PAGE only
        $dosesNeededOnThisPage = [];
        foreach($pageDays as $day) {
            $weekIndex = floor($start->diffInDays($day) / 7);
            if (isset($weeks[$weekIndex])) {
                $dosesThisWeek = $weeks[$weekIndex];
                // Track which row indices are needed
                // 1x: [0] = Morning
                // 2x: [0,3] = Morning, Bedtime
                // 3x: [0,2,3] = Morning, Supper, Bedtime
                // 4x: [0,1,2,3] = Morning, Midday, Supper, Bedtime
                if ($dosesThisWeek >= 4) {
                    $dosesNeededOnThisPage = array_merge($dosesNeededOnThisPage, [0, 1, 2, 3]);
                } elseif ($dosesThisWeek == 3) {
                    $dosesNeededOnThisPage = array_merge($dosesNeededOnThisPage, [0, 2, 3]);
                } elseif ($dosesThisWeek == 2) {
                    $dosesNeededOnThisPage = array_merge($dosesNeededOnThisPage, [0, 3]);
                } elseif ($dosesThisWeek == 1) {
                    $dosesNeededOnThisPage = array_merge($dosesNeededOnThisPage, [0]);
                }
            }
        }
        $dosesNeededOnThisPage = array_unique($dosesNeededOnThisPage);
        sort($dosesNeededOnThisPage);
        
        if (empty($dosesNeededOnThisPage)) {
            continue; // Skip if no active doses on this page
        }
        
        // Define labels for each row index
        $allLabels = [0 => 'Morning', 1 => 'Midday', 2 => 'Supper', 3 => 'Bedtime'];
        $rowCount = count($dosesNeededOnThisPage);
    @endphp

    @foreach($dosesNeededOnThisPage as $displayIndex => $row)
        <tr>
            @if($displayIndex === 0)
                <td class="med-name" rowspan="{{ $rowCount }}">
                    {{ $med['name'] }}
                    @if($med['notes'])
                        <br><small style="font-weight:normal;">{{ $med['notes'] }}</small>
                    @endif
                </td>
            @endif

            <td style="text-align:left; padding-left:8px; font-weight:bold; width:80px;">{{ $allLabels[$row] }}</td>

            @foreach($pageDays as $day)
                @php
                    // Determine which "week" this day falls into
                    $weekIndex = floor($start->diffInDays($day) / 7);
                    $dosesThisWeek = $weeks[$weekIndex] ?? 0;

                    // Determine if this row is active based on dose pattern:
                    // 1x: Morning (row 0)
                    // 2x: Morning (row 0) and Bedtime (row 3)
                    // 3x: Morning (row 0), Supper (row 2), Bedtime (row 3)
                    // 4x: all rows
                    $active = false;
                    if ($dosesThisWeek >= 4) {
                        $active = true; // all 4 rows active
                    } elseif ($dosesThisWeek == 3) {
                        $active = in_array($row, [0, 2, 3]); // Morning, Supper, Bedtime
                    } elseif ($dosesThisWeek == 2) {
                        $active = in_array($row, [0, 3]); // Morning and Bedtime
                    } elseif ($dosesThisWeek == 1) {
                        $active = ($row == 0); // Morning only
                    }
                @endphp

                @if($active)
                    <td class="checkbox-cell">&nbsp;</td>
                @else
                    <td class="checkbox-cell inactive">×</td>
                @endif
            @endforeach
        </tr>
    @endforeach
@endforeach
</tbody>

        </table>
    </div>
@endfor

</body>
</html>
