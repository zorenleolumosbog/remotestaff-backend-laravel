<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CancellationReasonTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // terminated
        $terminated = DB::table('tblm_contract_status')->where(DB::Raw('LOWER(description)'),'terminated')->first('id');
        if(! is_null($terminated)) {
            $terminated_id = $terminated->id;
            $types = [
                ['link_contract_status_id' => $terminated_id, 'description' => 'Project Based', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'BYO', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Buy-out', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Staff Attendance', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Staff Cannot be contacted', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Talent Mismatch', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Staff Performance Issue', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Staff Communication Problem', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Staff Resources', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Client Finances', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Client Not Ready', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Client cannot be contacted', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Redundancy', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Will Hire Locally', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'No Task from Client', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Client Business Restructuring', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Business Closure', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Change in Client Requirement', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Commitment and Reliability', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Cultural Differences', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Breach of Contract', 'datecreated' => now()],
                ['link_contract_status_id' => $terminated_id, 'description' => 'Others', 'datecreated' => now()],
            ];
            DB::table('tblm_cancellation_reason_type')->insert($types);
        }

        // resigned
        $resigned = DB::table('tblm_contract_status')->where(DB::Raw('LOWER(description)'),'resigned')->first('id');
        if(! is_null($resigned)) {
            $resigned_id = $resigned->id;
            $types = [
                ['link_contract_status_id' => $resigned_id, 'description' => 'Personal Health', 'datecreated' => now()],
                ['link_contract_status_id' => $resigned_id, 'description' => 'Health of Family Member/s', 'datecreated' => now()],
                ['link_contract_status_id' => $resigned_id, 'description' => 'Personal/Family Business', 'datecreated' => now()],
                ['link_contract_status_id' => $resigned_id, 'description' => 'Better Opportunities', 'datecreated' => now()],
                ['link_contract_status_id' => $resigned_id, 'description' => 'Professional Growth', 'datecreated' => now()],
                ['link_contract_status_id' => $resigned_id, 'description' => 'Studies', 'datecreated' => now()],
                ['link_contract_status_id' => $resigned_id, 'description' => 'Migrating', 'datecreated' => now()],
                ['link_contract_status_id' => $resigned_id, 'description' => 'Death in the family', 'datecreated' => now()],
                ['link_contract_status_id' => $resigned_id, 'description' => 'Irreconcilable Differences', 'datecreated' => now()],
                ['link_contract_status_id' => $resigned_id, 'description' => 'Personal Family Matters', 'datecreated' => now()],
                ['link_contract_status_id' => $resigned_id, 'description' => 'Others', 'datecreated' => now()]
            ];
            DB::table('tblm_cancellation_reason_type')->insert($types);
        }

        // invalid
        $invalid = DB::table('tblm_contract_status')->where(DB::Raw('LOWER(description)'),'invalid')->first('id');
        if(! is_null($invalid)) {
            $invalid_id = $invalid->id;
            $types = [
                ['link_contract_status_id' => $invalid_id, 'description' => 'Did Not Start', 'datecreated' => now()],
                ['link_contract_status_id' => $invalid_id, 'description' => 'Duplicate', 'datecreated' => now()],
                ['link_contract_status_id' => $invalid_id, 'description' => 'Re-contract: Change in Currency', 'datecreated' => now()],
                ['link_contract_status_id' => $invalid_id, 'description' => 'Re-contract: New Account Holder/ Same Company', 'datecreated' => now()],
                ['link_contract_status_id' => $invalid_id, 'description' => 'IT Testing', 'datecreated' => now()],
                ['link_contract_status_id' => $invalid_id, 'description' => 'Inhouse Cancellation', 'datecreated' => now()]
            ];
            DB::table('tblm_cancellation_reason_type')->insert($types);
        }
    }
}
