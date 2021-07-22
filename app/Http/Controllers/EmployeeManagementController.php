<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect,Config;
use Mail;
use Illuminate\Support\Facades\DB;
use Response;
use App\Employee;
use App\Department;
use App\Division;
use App\Http\Controllers\EmailController;
use Log;

class EmployeeManagementController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = DB::table('employees')
        ->join('department', 'employees.department_id', '=', 'department.id')
        ->join('division', 'employees.division_id', '=', 'division.id')
        ->select(
            'employees.*',
            'department.name as department_name',
            'department.id as department_id',
            'division.name as division_name',
            'division.id as division_id'
        )
        ->paginate(5);

        return view('employees-mgmt/index', ['employees' => $employees]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = Department::all();
        $divisions = Division::all();
        return view('employees-mgmt/create', [
            'departments' => $departments, 
            'divisions' => $divisions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ab = $this->validateInput($request);

        $keys = [
            'lastname',
            'firstname',
            'email',
            'address',
            'password',
            'age',
            'birthdate',
            'date_hired',
            'department_id',
            'department_id',
            'division_id'
        ];
        $input = $this->createQueryInput($keys, $request);
        if ($request->file('picture')) {
            $path = $request->file('picture')->store('avatars');
            $input['picture'] = $path;
        } else{
            $input['picture'] = '';
        }
        
        $input['email'] = $request['email'];
        $input['password'] = str_random(8);
        $this->sendEmail($input);
        Employee::create($input);

        return redirect()->intended('/employee-management');
    }


    public static function sendEmail($input)
    {
        $email=$input['email'];
        $firstname=$input['firstname'];
        $data['title'] = "Hi ".$firstname." Please check the password below and change once you logged in";
        $data['password'] = $input['password'];
        Log::info($data);
        Mail::send('email', $data, function($message)  use($email, $firstname) {
            $message->to($email, $firstname)
                    ->subject('New Password for the site');
        });
 
        if (Mail::failures()) {
            $data['message'] = 'Sorry! Please try again latter';
            $jsonResult = json_encode($data);
            $response = Response::make($jsonResult);
            $response->header('Content-Type', 'application/json');
            return $response;
         }else{
            $data['message'] = 'Great! Successfully send in your mail';
            $jsonResult = json_encode($data);
            $response = Response::make($jsonResult);
            $response->header('Content-Type', 'application/json');
            return $response;
           
         }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $departments = Department::all();
        $divisions = Division::all();
        return view('employees-mgmt/edit', [
            'employee' => $employee,
            'departments' => $departments, 
            'divisions' => $divisions
        ]);
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
        $employee = Employee::findOrFail($id);
        $this->validateInput($request);
        $keys = [
            'lastname',
            'firstname',
            'email',
            'address',
            'password',
            'age',
            'birthdate',
            'date_hired',
            'department_id',
            'department_id',
            'division_id'
        ];
        $input = $this->createQueryInput($keys, $request);
        if ($request->file('picture')) {
            $path = $request->file('picture')->store('avatars');
            $input['picture'] = $path;
        }

        Employee::where('id', $id)->update($input);

        return redirect()->intended('/employee-management');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
         Employee::where('id', $id)->delete();
         return redirect()->intended('/employee-management');
    }

    /**
     * Search state from database base on some specific constraints
     *
     * @param  \Illuminate\Http\Request  $request
     *  @return \Illuminate\Http\Response
     */
    public function search(Request $request) {
        $constraints = [
            'firstname' => $request['firstname'],
            'email' => $request['email']
            ];
        $employees = $this->doSearchingQuery($constraints);
        return view('employees-mgmt/index', [
            'employees' => $employees,
            'searchingVals' => $constraints
        ]);
    }

    private function doSearchingQuery($constraints) {
        $query = DB::table('employees')
        ->join('department', 'employees.department_id', '=', 'department.id')
        ->join('division', 'employees.division_id', '=', 'division.id')
        ->select(
            'employees.firstname as employee_name',
            'employees.*','department.name as department_name',
            'department.id as department_id',
            'division.name as division_name',
            'division.id as division_id'
        );
        $fields = array_keys($constraints);
        $index = 0;
        foreach ($constraints as $constraint) {
            if ($constraint != null) {
                $query = $query->where($fields[$index], 'like', '%'.$constraint.'%');
            }

            $index++;
        }
        return $query->paginate(5);
    }

     /**
     * Load image resource.
     *
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function load($name) {
         $path = storage_path().'/app/avatars/'.$name;
        if (file_exists($path)) {
            return Response::download($path);
        }
    }

    private function validateInput($request) {
        $this->validate($request, [
            'lastname' => 'required|max:60',
            'firstname' => 'required|max:60',
            'email' => 'required|max:60',
            'address' => 'required|max:120',
            'age' => 'required',
            'birthdate' => 'required',
            'date_hired' => 'required',
            'department_id' => 'required',
            'division_id' => 'required',
            'picture' => 'mimes:jpeg,bmp,png|max:5000',
        ]);
    }

    private function createQueryInput($keys, $request) {
        $queryInput = [];
        for($i = 0; $i < sizeof($keys); $i++) {
            $key = $keys[$i];
            $queryInput[$key] = $request[$key];
        }

        return $queryInput;
    }
}
