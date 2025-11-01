<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medication;
use PDF; // alias for Barryvdh\DomPDF\Facade\Pdf, ensure alias or use \Barryvdh\DomPDF\Facade\Pdf

class ChartController extends Controller
{
    public function form()
    {
        $medications = Medication::orderBy('name')->get();
        return view('chart.form', compact('medications'));
    }

    public function generate(Request $request){
    $request->validate([
        'start_date'=>'required|date',
        'medications'=>'required|array|max:4',
        'medications.*.id'=>'required|exists:medications,id',
        'medications.*.blocks'=>'required|array|min:1',
        'medications.*.blocks.*.weeks'=>'required|integer|min:1',
        'medications.*.blocks.*.doses'=>'required|integer|min:1|max:4',
    ]);

    $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
    $days = [];
    $maxWeeks = 0;

    // Determine total weeks to build top row
    foreach($request->medications as $med){
        $weeksSum = array_sum(array_column($med['blocks'],'weeks'));
        if($weeksSum>$maxWeeks) $maxWeeks=$weeksSum;
    }

    for($i=0;$i<$maxWeeks*7;$i++){
        $days[] = $start->copy()->addDays($i);
    }

    // Build meds array with expanded weekly schedule
    $meds = [];
    foreach($request->medications as $medEntry){
        $med = Medication::find($medEntry['id']);
        if(!$med) continue;

        $weeks_schedule = [];
        foreach($medEntry['blocks'] as $block){
            for($w=0;$w<$block['weeks'];$w++){
                $weeks_schedule[] = $block['doses'];
            }
        }

        $meds[] = [
            'name'=>$med->name,
            'notes'=>$med->notes,
            'weeks_schedule'=>$weeks_schedule,
            'start_date'=>$start
        ];
    }

    $pdf = \PDF::loadView('chart.pdf', [
        'meds'=>$meds,
        'days'=>$days,
        'start'=>$start,
        'weeks'=>$maxWeeks
    ])->setPaper('letter','landscape');

    return $pdf->download('eye-drop-chart-'.$start->format('Ymd').'.pdf');
}
}
