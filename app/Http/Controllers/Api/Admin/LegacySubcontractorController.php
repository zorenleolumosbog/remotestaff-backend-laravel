<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\LegacyPersonal;
use App\Models\Admin\LegacySubcontractor;
use App\Models\Users\OnboardProfileBasicInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LegacySubcontractorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $subcontractors = LegacyPersonal::
        select('userid', 'fname', 'lname')
        ->when($request->search, function ($query) use ($request) {
            $query->where(DB::raw('CONCAT_WS(" ", fname, lname)'), 'LIKE', "{$request->search}%");
        })
        ->with(['subcontractors' => function ($query) {
            $query->select('id', 'userid');
        }])
        ->has('subcontractors')
        ->orderBy('userid', 'desc')
        ->paginate($request->limit ? $request->limit : LegacyPersonal::count());

        if( $subcontractors->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $subcontractors,
        ], 200);
    }

    // public function update()
    // {
    //     $users = OnboardProfileBasicInfo::whereNotNull('legacy_subcon_id')->get();
    //     foreach ($users as $user) {
    //         $subcon = LegacySubcontractor::where('id', $user->legacy_subcon_id)->first();

    //         OnboardProfileBasicInfo::where('legacy_subcon_id', $user->legacy_subcon_id)->update([
    //             'legacy_user_id' => $subcon->userid
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Successfully updated.'
    //     ], 200);
    // }
}
