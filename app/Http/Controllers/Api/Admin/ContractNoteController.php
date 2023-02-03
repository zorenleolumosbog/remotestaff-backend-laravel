<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContractNote;

class ContractNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $contract_notes = ContractNote::leftJoin('tblm_filetype','tblm_filetype.id', '=', 'tblm_contract_notes.link_filetype_id')
        ->when($request->search, function ($query) use ($request) {
            $query->where('tblm_contract_notes.notes', 'LIKE', "{$request->search}%");
            $query->orWhere('tblm_filetype.description', 'LIKE', "{$request->search}%");
        })
        ->selectRaw('
            tblm_contract_notes.*,
            tblm_filetype.description AS filetype_description
        ')
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : ContractNote::count());

        if( $contract_notes->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $contract_notes,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$validator = Validator::make($request->all(), [
            'notes' => 'required|max:250'
        ],
        [
            'notes.required' => 'The Contract Note is required.',
            'notes.max' => 'The Contract Note must not exceed 250 characters.',
        ]);

		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        // validate file
        if(! empty($request->filetype_id) && empty($request->filename)) {
			return response()->json([
                'errors' => ['file' => ["The File is required."]]
            ], 422);
        }

        // file
        $filename = $path = null;
        if (! empty($request->filename) && ! empty($request->file)) {
            $filename = $request->filename;
            $base64_image = $request->file;
            @list($type, $file_data) = explode(';', $base64_image);
            @list(, $file_data) = explode(',', $file_data); 
            $filenameArray = explode('.',$filename);
            $path = 'contract_notes/'.\Str::random(10).'.'.$filenameArray[1];
            Storage::disk('public')->put($path, base64_decode($file_data));
        }

        $contract_note = ContractNote::create([
            'notes' => $request->notes,
            'link_filetype_id' => $request->filetype_id,
            'filename' => $filename,
            'path' => $path,
            'createdby' => auth()->user()->id,
            'datecreated' => now()
        ]);

		return response()->json([
            'success' => true,
            'message' => 'Successfully added.',
            'data' => $contract_note
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $contract_note = ContractNote::where('id', $id)->with('filetype')->first();

        if( !$contract_note ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $contract_note
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $contract_note = ContractNote::find($id);

        if( !$contract_note ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

		$validator = Validator::make($request->all(), [
            'notes' => 'required|max:250',
        ],
        [
            'notes.required' => 'The Contract Note is required.',
            'notes.max' => 'The Contract Note must not exceed 20 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        // validate file
        if(! empty($request->filetype_id) && empty($request->filename)) {
			return response()->json([
                'errors' => ['file' => ["The File is required."]]
            ], 422);
        }

        // file
        $filename = $path = null;
        if (! empty($request->filename) && ! empty($request->file)) {
            if(! empty($contract_note->path)) {
                Storage::disk('public')->delete($contract_note->path);
            }

            $filename = $request->filename;
            $base64_image = $request->file;
            @list($type, $file_data) = explode(';', $base64_image);
            @list(, $file_data) = explode(',', $file_data); 
            $filenameArray = explode('.',$filename);
            $path = 'contract_notes/'.\Str::random(10).'.'.$filenameArray[1];
            Storage::disk('public')->put($path, base64_decode($file_data));
        }

        $contract_note->notes = $request->notes;
        $contract_note->link_filetype_id = $request->filetype_id;
        $contract_note->filename = $filename;
        $contract_note->path = $path;
        $contract_note->modifiedby = auth()->user()->id;
        $contract_note->datemodified = now();
        $contract_note->save();

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => $contract_note
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $contract_note = ContractNote::find($id);

        if( !$contract_note ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        if(! empty($contract_note->path)) {
            Storage::disk('public')->delete($contract_note->path);
        }

        $contract_note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
