<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\EnquiryComment;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\EnquiryType;
use App\Models\Source;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class EnquiryController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Enquiries';
        if ($request->wantsJson()) {
            $data = Enquiry::with(['enquiryType','source', 'service', 'serviceItem', 'assignedTo'])->orderBy('id','desc')->get();
            return $this->success($data);
        }
        return view('master.enquiry.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'New Enquiry';
        $projects = Project::orderBy('name')->get();
        $enquiryTypes = EnquiryType::orderBy('name')->get();
        $sources = Source::orderBy('name')->get();
        $services = \App\Models\Service::orderBy('category')->orderBy('name')->get()->groupBy('category');
        $users = \App\Models\User::orderBy('name')->get();
        return view('master.enquiry.create', compact('heading','projects','enquiryTypes','sources', 'services', 'users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:255',
            'gstin' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'enquiry_type_id' => 'nullable|exists:enquiry_types,id',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:100',
            'priority' => 'nullable|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'source_id' => 'nullable|exists:sources,id',
            'service_id' => 'nullable|exists:services,id',
            'service_item_id' => 'nullable|exists:service_items,id',
            'next_follow_up_at' => 'nullable|date',
            'reminder_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) return response()->json(['status'=>'error','errors'=>$validator->errors()],422);

        $data = $request->only([
            'mobile','name','email','location','gstin','address',
            'enquiry_type_id','project_id','description','status','priority','assigned_to',
            'source_id', 'service_id', 'service_item_id', 'next_follow_up_at','reminder_notes'
        ]);
        $enquiry = Enquiry::create($data);

        // create initial follow-up record if a next_follow_up_at was provided (avoid duplicates)
        if (!empty($data['next_follow_up_at'])) {
            try {
                $exists = $enquiry->followUps()->where('scheduled_at', $data['next_follow_up_at'])->exists();
                if (! $exists) {
                    $enquiry->followUps()->create([
                        'scheduled_at' => $data['next_follow_up_at'],
                        'notes' => $data['reminder_notes'] ?? null,
                        'created_by' => Auth::id()
                    ]);
                }
            } catch (\Exception $e) { /* ignore */ }
        }

        return $this->success($enquiry,'Enquiry created successfully');
    }

    public function edit($id)
    {
        $heading = 'Edit Enquiry';
        $enquiry = Enquiry::findOrFail($id);
        $projects = Project::orderBy('name')->get();
        $enquiryTypes = EnquiryType::orderBy('name')->get();
        $sources = Source::orderBy('name')->get();
        $services = \App\Models\Service::orderBy('category')->orderBy('name')->get()->groupBy('category');
        $users = \App\Models\User::orderBy('name')->get();
        return view('master.enquiry.edit', compact('heading','enquiry','projects','enquiryTypes','sources', 'services', 'users'));
    }

    public function update(Request $request, $id)
    {
        $enquiry = Enquiry::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'mobile' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:255',
            'gstin' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'enquiry_type_id' => 'nullable|exists:enquiry_types,id',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:100',
            'priority' => 'nullable|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'source_id' => 'nullable|exists:sources,id',
            'service_id' => 'nullable|exists:services,id',
            'service_item_id' => 'nullable|exists:service_items,id',
            'next_follow_up_at' => 'nullable|date',
            'reminder_notes' => 'nullable|string'
        ]);
        if ($validator->fails()) return response()->json(['status'=>'error','errors'=>$validator->errors()],422);

        $data = $request->only([
            'mobile','name','email','location','gstin','address',
            'enquiry_type_id','project_id','description','status','priority','assigned_to',
            'source_id', 'service_id', 'service_item_id', 'next_follow_up_at','reminder_notes'
        ]);
        // detect change in next_follow_up_at to store history
        $oldNext = $enquiry->next_follow_up_at ? $enquiry->next_follow_up_at->toDateTimeString() : null;
        $newNext = isset($data['next_follow_up_at']) ? $data['next_follow_up_at'] : null;

        $enquiry->update($data);

        if ($newNext && $newNext !== $oldNext) {
            try {
                $exists = $enquiry->followUps()->where('scheduled_at', $newNext)->exists();
                if (! $exists) {
                    $enquiry->followUps()->create([
                        'scheduled_at' => $newNext,
                        'notes' => $data['reminder_notes'] ?? null,
                        'created_by' => Auth::id()
                    ]);
                }
            } catch (\Exception $e) { /* ignore */ }
        }

        return $this->success($enquiry,'Enquiry updated successfully');
    }

    // Add a comment to an enquiry
    public function storeComment(Request $request, $id)
    {
        $enquiry = Enquiry::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'body' => 'required|string'
        ]);
        if ($validator->fails()) return response()->json(['status'=>'error','errors'=>$validator->errors()],422);

        $comment = $enquiry->comments()->create([
            'body' => $request->input('body'),
            'user_id' => Auth::id()
        ]);

        return $this->success($comment,'Comment added');
    }

    // Remove a comment
    public function destroyComment($id, $commentId)
    {
        $enquiry = Enquiry::findOrFail($id);
        $comment = $enquiry->comments()->where('id',$commentId)->firstOrFail();
        $comment->delete();
        return $this->success([],'Comment deleted');
    }

    public function destroy($id)
    {
        $enquiry = Enquiry::findOrFail($id);
        $enquiry->delete();
        return $this->success([],'Enquiry deleted successfully');
    }

    // Store a follow-up for an enquiry (created from the show page)
    public function storeFollowUp(Request $request, $id)
    {
        $enquiry = Enquiry::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date',
            'notes' => 'nullable|string'
        ]);
        if ($validator->fails()) return response()->json(['status'=>'error','errors'=>$validator->errors()],422);

        $scheduled = $request->input('scheduled_at');
        $notes = $request->input('notes');

        try {
            // avoid creating an exact duplicate
            $existing = $enquiry->followUps()->where('scheduled_at', $scheduled)->first();
            if ($existing) {
                $existing->load('user');
                // still update enquiry next_follow_up_at to this value
                $enquiry->update(['next_follow_up_at' => $scheduled, 'reminder_notes' => $notes ?? $enquiry->reminder_notes]);
                return $this->success($existing,'Follow-up already exists');
            }

            $follow = $enquiry->followUps()->create([
                'scheduled_at' => $scheduled,
                'notes' => $notes,
                'created_by' => Auth::id()
            ]);

            // also update the enquiry's next_follow_up_at to reflect this scheduling
            $enquiry->update(['next_follow_up_at' => $scheduled, 'reminder_notes' => $notes ?? $enquiry->reminder_notes]);

            // reload with user relation if available
            $follow->load('user');
            return $this->success($follow,'Follow-up created');
        } catch (\Exception $e) {
            return response()->json(['status'=>'error','message'=>'Unable to create follow-up'],500);
        }
    }

    public function show(Request $request, $id)
    {
        $enquiry = Enquiry::with(['project','enquiryType','source','service','serviceItem','comments.user','followUps.user', 'assignedTo'])->findOrFail($id);
        if ($request->wantsJson()) {
            return $this->success($enquiry);
        }
        $heading = 'Enquiry Details';
        
        // Fetch activity logs for this enquiry
        $activities = \Spatie\Activitylog\Models\Activity::where('subject_type', Enquiry::class)
            ->where('subject_id', $id)
            ->with('causer')
            ->latest()
            ->get();

        return view('master.enquiry.show', compact('heading','enquiry', 'activities'));
    }

    public function checkName(Request $request)
    {
        $name = $request->input('name'); $excludeId = $request->input('exclude_id');
        $normalQuery = Enquiry::where('name',$name)->whereNull('deleted_at'); if ($excludeId) $normalQuery->where('id','!=',$excludeId);
        if ($normalQuery->exists()) return response()->json(['exists'=>true,'trashed'=>false]);
        $trashed = Enquiry::onlyTrashed()->where('name',$name)->when($excludeId, function($q) use($excludeId){ $q->where('id','!=',$excludeId); })->first();
        if ($trashed) return response()->json(['exists'=>true,'trashed'=>true,'id'=>$trashed->id]);
        return response()->json(['exists'=>false]);
    }
}
