<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contracts;
use App\Models\Employees;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ContractController extends Controller
{
    public function signUp(Request $request)
    {
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);

        Company::create([
            "companyName" => $request->companyName,
            "user_id" => $user->id,
        ]);

        return response()->json(["User and Company Created Successfull"]);
    }

    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required|string|min:6",
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()->all()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where("email", $request->email)->first();

        if (!$user) {
            return \response()->json(["errors" => ["User not found"]], Response::HTTP_NOT_FOUND);
        }

        if (!Hash::check($request->password, $user->password)) {
            return \response()->json(["errors" => ["Invalid credentials"]], Response::HTTP_FORBIDDEN);
        }
        Auth::attempt([
            "email" => $request->email,
            "password" => $request->password,
        ]);

        $company = DB::selectOne("SELECT Company.id, Company.companyName AS companyName FROM
                                  Company INNER JOIN users ON Company.user_id = users.id
                                  WHERE users.id = ?", [$user->id]);

        return \response()->json([
            "names" => $user->name,
            "companyName" => $company->companyName,
            "company_id" => $company->id,
        ]);
    }

    public function registerContract(Request $request)
    {
        $employee = Employees::create([
            "names" => $request->names,
            "status" => "active",
        ]);

        Contracts::create([
            "company_id" => $request->company_id,
            "employee_id" => $employee->id,
            "contractType" => $request->contractType,
            "startDate" => $request->startDate,
            "endDate" => $request->endDate,
            "description" => $request->description,
        ]);

        return response()->json(["Employee Contract Created successfully"]);
    }

    public function retreiveContract(int $companyID)
    {
        $contractsCompany = DB::select("SELECT Contracts.id AS contractID, Contracts.contractType AS contractTitle, 
                                        Employees.names AS contractEmployeeName, 
                                        CONCAT(Contracts.startDate, '', 'To', '',  Contracts.endDate) AS dateCreatedAndExpired, 
                                        Contracts.description AS contractDescription
                                        FROM Contracts 
                                        INNER JOIN Employees ON Contracts.employee_id = Employees.id 
                                        INNER JOIN Company ON Contracts.company_id = Company.id 
                                        WHERE Company.id = ?", [$companyID]);

        return response()->json($contractsCompany);
    }

    public function retreiveContractDetails(int $contractID)
    {
        $contracts = DB::selectOne("SELECT Employees.names AS employeeNames, Contracts.contractType,
                                    Contracts.startDate,Contracts.endDate, Contracts.description
                                    FROM Contracts INNER JOIN Employees ON Contracts.employee_id = Employees.id
                                    INNER JOIN Company ON Contracts.company_id = Company.id
                                    WHERE Contracts.id = ?", [$contractID]);

        return response()->json([
            "employeeNames" => $contracts->employeeNames,
            "contractType" => $contracts->contractType,
            "startDate" => $contracts->startDate,
            "endDate" => $contracts->endDate,
            "description" => $contracts->description
        ]);
    }

    public function dashboardContract(int $companyID)
    {
        
    }
}
