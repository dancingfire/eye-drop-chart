<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medication;
use PDF; // alias for Barryvdh\DomPDF\Facade\Pdf, ensure alias or use \Barryvdh\DomPDF\Facade\Pdf

class ChartController extends Controller
{
    public function form()
    {
        $medications = Medication::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();
        return view('chart.form', compact('medications'));
    }

    public function generate(Request $request){
    $request->validate([
        'start_date'=>'required|date',
        'surgery_date'=>'nullable|date',
        'medications'=>'required|array',
        'medications.*.id'=>'required|exists:medications,id',
        'medications.*.blocks'=>'required|array|min:1',
        'medications.*.blocks.*.days'=>'required|integer|min:1|max:70',
        'medications.*.blocks.*.doses'=>'required|integer|min:0|max:4',
    ]);

    $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
    $surgeryDate = $request->surgery_date ? \Carbon\Carbon::parse($request->surgery_date)->startOfDay() : null;
    $days = [];
    $maxDays = 0;

    // Determine total days to build calendar
    foreach($request->medications as $med){
        $daysSum = array_sum(array_column($med['blocks'],'days'));
        if($daysSum>$maxDays) $maxDays=$daysSum;
    }

    for($i=0;$i<$maxDays;$i++){
        $days[] = $start->copy()->addDays($i);
    }

    // Build meds array with expanded daily schedule
    $meds = [];
    foreach($request->medications as $medEntry){
        $med = Medication::find($medEntry['id']);
        if(!$med) continue;

        $days_schedule = [];
        foreach($medEntry['blocks'] as $block){
            for($d=0;$d<$block['days'];$d++){
                $days_schedule[] = $block['doses'];
            }
        }

        $meds[] = [
            'name'=>$med->name,
            'notes'=>$med->notes,
            'days_schedule'=>$days_schedule,
            'start_date'=>$start
        ];
    }

    $pdf = \PDF::loadView('chart.pdf', [
        'meds'=>$meds,
        'days'=>$days,
        'start'=>$start,
        'surgeryDate'=>$surgeryDate,
        'user'=>auth()->user()
    ])->setPaper('letter','landscape');

    return $pdf->download('eye-drop-chart-'.$start->format('Ymd').'.pdf');
}

public function htmlchart(Request $request){
    $request->validate([
        'start_date'=>'required|date',
        'surgery_date'=>'nullable|date',
        'medications'=>'required|array',
        'medications.*.id'=>'required|exists:medications,id',
        'medications.*.blocks'=>'required|array|min:1',
        'medications.*.blocks.*.days'=>'required|integer|min:1|max:70',
        'medications.*.blocks.*.doses'=>'required|integer|min:0|max:4',
    ]);

    $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
    $surgeryDate = $request->surgery_date ? \Carbon\Carbon::parse($request->surgery_date)->startOfDay() : null;
    $days = [];
    $maxDays = 0;

    // Determine total days to build calendar
    foreach($request->medications as $med){
        $daysSum = array_sum(array_column($med['blocks'],'days'));
        if($daysSum>$maxDays) $maxDays=$daysSum;
    }

    for($i=0;$i<$maxDays;$i++){
        $days[] = $start->copy()->addDays($i);
    }

    // Build meds array with expanded daily schedule
    $meds = [];
    foreach($request->medications as $medEntry){
        $med = Medication::find($medEntry['id']);
        if(!$med) continue;

        $days_schedule = [];
        foreach($medEntry['blocks'] as $block){
            for($d=0;$d<$block['days'];$d++){
                $days_schedule[] = $block['doses'];
            }
        }

        $meds[] = [
            'name'=>$med->name,
            'notes'=>$med->notes,
            'days_schedule'=>$days_schedule,
            'start_date'=>$start
        ];
    }

    return view('chart.pdf', [
        'meds'=>$meds,
        'days'=>$days,
        'start'=>$start,
        'surgeryDate'=>$surgeryDate,
        'user'=>auth()->user()
    ]);
}
}
