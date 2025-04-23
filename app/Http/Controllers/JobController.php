<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use PhpParser\Node\Expr\Cast\String_;
use Illuminate\Support\Facades\Storage; //this is accessing storage & delete previous file logo
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; //use in policy authorization (prevent unauthorize user to edit or delete)

class JobController extends Controller
{

    use AuthorizesRequests;

    //@desc Show all job listings
    //@route GET /jobs
    public function index(): View
    {
        // $jobs = [
        //     'Web Developer',
        //     'Database Admin',
        //     'Software Engineer',
        //     'System Analyst'
        // ];
        
        // $jobs = Job::all();
        $jobs = Job::latest()->paginate(9);
        
        // return view('jobs.index', compact('jobs'));
        return view('jobs.index')->with('jobs', $jobs);
    }

    //@desc Show create job form
    //@route GET /jobs/create
    public function create(): View
    {
        return view('jobs.create');
    }

    //@desc Save job to database
    //@route POST /jobs
    public function store(Request $request): RedirectResponse
    {
        // $title = $request->input('title');
        // $description = $request->input('description');

        // dd($request->file('company_logo'));

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|integer',
            'tags' => 'nullable|string',
            'job_type' => 'required|string',
            'remote' => 'required|boolean',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'nullable|string',
            'contact_email' => 'required|string',
            'contact_phone' => 'nullable|string',
            'company_name' => 'required|string',
            'company_description' => 'nullable|string',
            'company_logo' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            'company_website' => 'nullable|url',
        ]);

        // Job::create([
        //     // 'title' => $title,
        //     // 'description' => $description,

        //     'title' => $validatedData['title'],
        //     'description' => $validatedData['description'],
        // ]);


        //Hardcoded user ID
        // $validatedData['user_id'] = 1;
        $validatedData['user_id'] = auth()->user()->id;

        //check for image
        if($request->hasFile('company_logo')){
            //Store the file and get path
            $path = $request->file('company_logo')->store('logos', 'public');

            //Add path to validated data
            $validatedData['company_logo'] = $path;
        }

        //Submit to database
        Job::create($validatedData);

        return redirect()->route('jobs.index')->with('success', 'Job listing created successfully!');
    }

    //@desc Display a single job listing
    //@route GET /jobs/{$id}
    public function show(Job $job): View //"Job $job is from the Model
    {
        return view('jobs.show')->with('job', $job);
    }

    //@desc Show edit job form
    //@route GET /jobs/{$id}/edit
    public function edit(Job $job): View
    {
        //check if user is authorized
        $this->authorize('update', $job);
        
        return view('jobs.edit')->with('job', $job);
    }

    //@desc Update job listing
    //@route PUT /jobs/{$id}
    public function update(Request $request, Job $job): string //"Job $job is from the Model
    {

        //check if user is authorized
        $this->authorize('update', $job);

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|integer',
            'tags' => 'nullable|string',
            'job_type' => 'required|string',
            'remote' => 'required|boolean',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'nullable|string',
            'contact_email' => 'required|string',
            'contact_phone' => 'nullable|string',
            'company_name' => 'required|string',
            'company_description' => 'nullable|string',
            'company_logo' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            'company_website' => 'nullable|url',
        ]);

        //Hardcoded user ID
        // $validatedData['user_id'] = 1;

        //check for image
        if($request->hasFile('company_logo')){
            //Delete old logo
            Storage::delete('public/logos/' . basename($job->company_logo));

            //Store the file and get path
            $path = $request->file('company_logo')->store('logos', 'public');

            //Add path to validated data
            $validatedData['company_logo'] = $path;
        }

        //Submit to database
        $job->update($validatedData);

        return redirect()->route('jobs.index')->with('success', 'Job listing updated successfully!');
        
    }

    //@desc Delete job listing
    //@route DELETE /jobs/{$id}
    public function destroy(Job $job): RedirectResponse //"Job $job is from the Model
    {

        //check if user is authorized
        $this->authorize('delete', $job);

        //if logo exist, then delete it
        if($job->company_logo) {
            Storage::delete('public/logos/' . $job->company_logo);
        }

        $job->delete();

        //check if request came from the dashboard
        if(request()->query('from') == 'dashboard'){
            return redirect()->route('dashboard')->with('success', 'Job listing deleted successfully!');    
        }

        return redirect()->route('jobs.index')->with('success', 'Job listing deleted successfully!');
    }

    // @desc Search job listings
    // @route GET /jobs/search
    public function search(Request $request): View 
    {
        $keywords=strtolower($request->input('keywords'));
        $location=strtolower($request->input('location'));

        // dd($keywords, $location);

        $query = Job::query();

        if($keywords) {
            $query->where(function ($q) use ($keywords) {
                $q->whereRaw('LOWER(title) like ?', ['%' . $keywords . '%'])
                ->orWhereRaw('LOWER(description) like ?', ['%' . $keywords . '%'])
                ->orWhereRaw('LOWER(tags) like ?', ['%' . $keywords . '%']);
            });
        }

        if($location) {
            $query->where(function ($q) use ($location) {
                $q->whereRaw('LOWER(address) like ?', ['%' . $location . '%'])
                ->orWhereRaw('LOWER(city) like ?', ['%' . $location . '%'])
                ->orWhereRaw('LOWER(state) like ?', ['%' . $location . '%'])
                ->orWhereRaw('LOWER(zipcode) like ?', ['%' . $location . '%']);
            });
        }

        $jobs = $query->paginate(12);

        return view('jobs.index')->with('jobs', $jobs);

    }

}
