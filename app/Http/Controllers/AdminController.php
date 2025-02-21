<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct( )
    {
        $this->pagesize = env('NUMBER_PER_PAGE','20');
        $this->middleware('admin.auth');
    }
    public function index()
    {
        //
       
        $func = "admin_view";
        if(!$this->check_function($func))
        {
            return redirect()->route('home');
        }
        $data['breadcrumb'] = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Bảng điều khiển</li>';
       $data['active_menu']='dashboard';
        return view ('backend.index',   $data);
   
        // echo 'i am admin';
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
