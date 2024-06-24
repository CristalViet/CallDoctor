<?php

namespace App\Actions\Fortify;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Map;
use App\Models\UserDetails;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();
        
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'type' => 'doctor',
            'password' => Hash::make($input['password']),
        ]);

        $doctorInfo = Doctor::create([
            'doc_id' => $user->id,
            'status' => 'active'
        ]);

        Map::create([
            'doctor_id' => $doctorInfo->id,
            'user_id' => $user->id,
            'location' => '', 
            'longitude' => 0.0000000, 
            'latitude' => 0.0000000, 
            'user_detail_id' => null,
        ]);

        return $user;
    }
}
