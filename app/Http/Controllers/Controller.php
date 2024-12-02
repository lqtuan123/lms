<?php

 


namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public function __construct( )
    {
        // $theme = \App\Models\Themesetting::where('selected',1)->first();
        // $this->front_view=$theme->title;//"frontend_tp";
       
    }
    
    public function check_function($func)
    {
        $user= auth()->user();
        if($user->role == 'admin')
            return true;
        $row = \DB::select("select d.value from (select * from (select id as role_id from roles where alias ='".$user->role
        ."') as a join (select id as cfunction_id from cmd_functions where alias = '".$func
        ."') as b) as c left join (select * from role_functions where value = 1) as d on c.role_id = d.role_id and c.cfunction_id = d.cfunction_id");
        if(count($row)> 0 && $row[0]->value)
            return true;
        else
            return false;
    }
}