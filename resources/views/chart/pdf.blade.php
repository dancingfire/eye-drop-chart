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

/* Thick border at the bottom of the last row of each medication */
tr.med-last-row td {
    border-bottom: 3px solid #000 !important;
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
@php
    $companyName = $user->company_name ?? 'Southeast Wellness Pharmacy';
    $companyPhone = $user->phone_number ?? '';
@endphp

@if($user->logo_path)
<div style="text-align: center; margin-bottom: 10px;">
    <img src="{{ public_path('storage/' . $user->logo_path) }}" alt="Logo" style="max-height: 60px; max-width: 200px;">
</div>
@endif

<h2 style="text-align:center;">Eye Drop Chart - {{ $companyName }} [X = No Drops]</h2>
<div style="text-align: center; margin-top: -15px">{{ $companyPhone }}</div>
@if($surgeryDate)
<div style="text-align: center; margin-top: 5px; font-weight: bold;">Surgery Date: {{ $surgeryDate->format('l, F j, Y') }}</div>
@endif

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
            $totalDaysForMed = count($med['days_schedule']);
            $medStart = \Carbon\Carbon::parse($med['start_date']);
            $medEndDate = $medStart->copy()->addDays($totalDaysForMed);
            
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
        $daysSchedule = $med['days_schedule'];
        $start = \Carbon\Carbon::parse($med['start_date']);
        
        // Check if this medication is active on this page
        $totalDaysForMed = count($daysSchedule);
        $medEndDate = $start->copy()->addDays($totalDaysForMed);
        $pageStartDate = $pageDays[0] ?? null;
        
        // Skip this medication if it has ended before this page starts
        if ($pageStartDate && $medEndDate->lessThan($pageStartDate)) {
            continue;
        }
        
        // Always show all 4 rows
        $standardLabels = ['Morning', 'Midday', 'Supper', 'Bedtime'];
    @endphp

    @for($row = 0; $row < 4; $row++)
        <tr{!! $row === 3 ? ' class="med-last-row"' : '' !!}>
            @if($row === 0)
                <td class="med-name" rowspan="4">
                    {{ $med['name'] }}
                    @if($med['notes'])
                        <br><small style="font-weight:normal;">{{ $med['notes'] }}</small>
                    @endif
                </td>
            @endif

            <td style="text-align:left; padding-left:8px; font-weight:bold; width:80px;">{{ $standardLabels[$row] }}</td>

            @foreach($pageDays as $day)
                @php
                    // Determine which day index this day falls into (0-based)
                    $dayIndex = $start->diffInDays($day);
                    $dosesThisDay = $daysSchedule[$dayIndex] ?? 0;

                    // Determine if this row is active based on dose pattern:
                    // 1x: Morning (row 0)
                    // 2x: Morning (row 0) and Bedtime (row 3)
                    // 3x: Morning (row 0), Supper (row 2), Bedtime (row 3)
                    // 4x: all rows
                    $active = false;
                    if ($dosesThisDay >= 4) {
                        $active = true; // all 4 rows active
                    } elseif ($dosesThisDay == 3) {
                        $active = in_array($row, [0, 2, 3]); // Morning, Supper, Bedtime
                    } elseif ($dosesThisDay == 2) {
                        $active = in_array($row, [0, 3]); // Morning and Bedtime
                    } elseif ($dosesThisDay == 1) {
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
    @endfor
@endforeach
</tbody>

        </table>
    </div>
@endfor

</body>
</html>
