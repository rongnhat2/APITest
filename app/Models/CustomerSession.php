<?php

namespace App\Models;

class CustomerSession
{
	public $customer = null;

	public function __construct($customer){
		if($customer){
			$this->customer = $customer;
		}
	}

    public function Create($user){
        $id 	= $user->id;
        $name 	= $user->name;
        $email 	= $user->email;
        $data 	= ['id' => $id, 'name' => $name, 'email' => $email];
        $this->customer = $data;=
    }
}
