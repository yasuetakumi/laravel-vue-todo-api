<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use App\Models\User;
use Auth;

class UsersImport implements ToCollection, WithHeadingRow, WithCustomCsvSettings, WithValidation
{
    /** 
     * create or update
    */
    protected $type;

    private $arrImportUsers = [];
    
    /** 
     * error message container
    */
    private $err_msg = [];

    /**
     * Array that contains all imported data
     */
    private $resultData = [];

    /**
     * List array of JA text for boolean value
     */
    private $arrBoolJapanText = [
        'User Role Id' => [
            "admin"    => 1,
            "user"     => 2
        ],
    ];

    /** 
     * List column on table users
    */
    public $columns = [ 
        'id'                => 'id',
        'user_role_d'       => 'user_role_id',
        'display_name'      => 'display_name',
        'email'             => 'email',
        'email_verified_at' => 'email_verified_at',
        'password'          => 'password',
        'create_at'         => 'created_at',
        'update_at'         => 'updated_at',
    ];

    public function  __construct($type){
        $this->type = $type;
    }

    private function validator($data, $index){
        $arrValidator = [
            'id'                => 'nullable|integer|exists:users,id',
            'user_role_d'       => 'required_without:id',
            'display_name'      => 'required_without:id|max:255',
            'email'             => 'required_without:id|email|max:255',
            'email_verified_at' => 'required_without:id|date',
            'password'          => 'required_without:id|min:8|max:30' // for now max 30
        ];

        return Validator::make($data, $arrValidator, $message = [
            '*.required_without' => 'The :attribute on the line '.$index.' is required item.',
            '*.required' => 'The :attribute on the line '.$index.' is required item.',
            '*' => 'The value of :attribute on line '.$index.' is incorrect.'
        ]);
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        // check email in csv is all unique
        $this->check_email_csv($rows);
        // check is error in prepareForValidation
        if(count($this->err_msg) > 0) {
            throw ValidationException::withMessages([
                "errors" => $this->err_msg,
            ]);
        }

        $this->arrImportUsers = [];
        foreach($rows as $indexs => $row){
            $this->validator($row->toArray(), $indexs + 1)->validate();
            $arrayInsert = [];
            $index = 1;
            foreach($row as $key=>$data){
                if(array_key_exists($key, $this->columns)){
                    /**
                     * If Update, null field included
                     * Else (create), null field not included 
                     */
                    if(isset($row['id'])){
                        if(!empty($row['id'])){
                            /**
                             * If password field, encrypt it first
                             * Else insert normaly
                             */
                            if($key == "password"){
                                $arrayInsert[$this->columns[$key]] = bcrypt($data);
                            }else{
                                $arrayInsert[$this->columns[$key]] = $data;
                            }
                        }
                    }else{
                        if(!is_null($data)){
                            /**
                             * If password field, encrypt it first
                             * Else insert normaly
                             */
                            if($key == "password"){
                                $arrayInsert[$this->columns[$key]] = bcrypt($data);
                            }else{
                                $arrayInsert[$this->columns[$key]] = $data;
                            }
                        }
                    }
                }else{
                    $this->throwError(true, $index, $key, null, true);
                }
                $index++;
            }
            
            // User Update
            if(isset($row['id'])){
                if(!empty($row['id'])){
                    $type = "update";
                    User::find($row['id'])->update($arrayInsert);

                }else{
                    // User Create
                    $type = "create";
                    $User = User::create($arrayInsert);
                }
            }else{
                // User Create
                $type = "create";
                $User = User::create($arrayInsert);
            }

            $this->resultData[$indexs] = [
                "data"  => $User,
                "type"  => $type
            ];

            $this->arrImportUsers[] = $User->id;
        }
    }

    public function prepareForValidation($data, $index)
    {   
        /**
         * Set maximum row that can be import is 50
         * The $index - 1 because index is include with header
         */
        $maximum_row_imported = 1;
        if(($index - 1) > $maximum_row_imported){
            throw ValidationException::withMessages(["errors" => "You can only import up to ".$maximum_row_imported]);
        }


        // Remove created_at and updated_at field from csv
        unset($data['create_at']);
        unset($data['update_at']);
        
        // Check if User import type is create or update
        if($this->type == "create"){
            if(array_key_exists('id', $data) && !empty($data["id"])){
                $index--;
                $this->throwError(false, $index, null, "ID in line ".$index." is entered. Please leave it blank.", true);
            }
        }else if($this->type == "update"){
            if(!array_key_exists('id', $data) || empty($data["id"])){
                $index--;
                $this->throwError(false, $index, null, "ID in the line ".$index." is empty. Please enter", true);
            }else{
                // Check if id exist
                $model = User::find($data["id"]);
                if($model == null){
                    $index--;
                    $this->throwError(false, $index, null, "ID in line ".$index." not found ", true);
                }
            }
        }

        // Check email unique
        if(array_key_exists('email', $data) && !empty($data["email"])){
            if(array_key_exists('id', $data) && !empty($data["id"])){
                $model = User::where('email', $data["email"])->where('id', '!=' , $data["id"])->first();
            }else{
                $model = User::where('email', $data["email"])->first();
            }
            if($model != null){
                if($this->type == "create") {
                    $this->throwError(false, $index, "email", "There is a duplicate email address on the line ".--$index, true);
                }
            }
        }
        
        /**
         * Convert field with date value dd/mm/yyyy to dd-mm-yyyy
         */
        if(array_key_exists('email_verified_at', $data) && !empty($data['email_verified_at'])){
            $var = $data['email_verified_at'];
            $date = str_replace('/', '-', $var);
            $data['email_verified_at'] = date('Y-m-d', strtotime($date));
        }

        /**
         * Validate if boolean field have valid value.
         */
        foreach($this->arrBoolJapanText as $key=>$JaField){
            // If Boolean field exist and not empty in csv file
            if(array_key_exists($key, $data) && !empty($data[$key])){
                if(array_key_exists($data[$key], $JaField)){
                    $data[$key] = $JaField[$data[$key]];
                }else{
                    $this->throwError(false, $index, $key, null, true);
                }
            }
        }

        return $data;
    }

    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'UTF-8',
        ];
    }

    /**
     * Return all created or updateded data
     */
    public function getResultData(){
        $arrUsersName = "";
        foreach($this->resultData as $user){
            $message = $user['type'] == "update" ? " has been updated." : " has been added.";
            $arrUsersName .= $user['data']->full_name.$message."<br/>";
        }
        return $arrUsersName;
    }

    public function getArrUsers() {
        return $this->arrImportUsers;
    }

    /**
     * Throw Error if header name not correct or field value not exist in relation table
     */
    public function throwError($isHeaderError, $index ,$headerName = null, $customMessage = null, $save_on_container = false){

        // Index decreament because except header
        $index--;

        if($customMessage != null){
            if($save_on_container) {
                // save error message to container
                $this->err_msg[] = $customMessage;
                return;
            }
            else {
                throw ValidationException::withMessages(["errors" => $customMessage]);
            }
        }

        if($isHeaderError){
            if($save_on_container) {
                // save error message to container
                $this->err_msg[] =  $index." The text in the column header「".$headerName."」is incorrect.";
                return;
            }
            else {
                throw ValidationException::withMessages(["errors" => $index." The text in the column header「".$headerName."」is incorrect"]);
            }
        }else{
            if($save_on_container) {
                // save error message to container
                $this->err_msg[] =  $index." The text in the column header「".$headerName."」is incorrect";
                return;
            }
            else {
                throw ValidationException::withMessages(["errors" => " The value of ".$headerName." In the line " .$index. " is incorrect"]);
            }
        }


    }

    /** 
     * check is all email in csv is unique
    */
    public function check_email_csv($rows)
    {   
        $emails = [];
        foreach($rows as $indexs => $row){
            foreach($row as $key => $data){
                if($key == "email" && !empty($data)){
                    $emails[] = $data;
                }
            }
        }
        $result = array_unique($emails);
        if(count($emails) != count($result)) {
            $this->throwError(false, 0, null, "There is a duplicate email address in the CSV.", true);
        }
        return;
    }
}
