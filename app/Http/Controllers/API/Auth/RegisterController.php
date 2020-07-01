<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Student;
use Illuminate\Support\Facades\Validator;
use App\Traits\AuthenticateWithJWT;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use AuthenticateWithJWT;

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|string|unique:users,email',
            'phone_number' => 'nullable|string|max:255',
            'mobile_number' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'account_type' => 'required|string',
            'children' => 'nullable'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'mobile_number' => $data['mobile_number'],
            'address' => $data['address'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['account_type']);

        switch ($data['account_type']) {
            case 'student':
                $student = new Student;
                $student->user_id = $user->id;
                $student->save();
                break;
            default:
                // code...
                break;
        }

        return $user;
    }

    protected function createAccountForChildren(array $children, array $data, $parentId)
    {
        foreach ($children as $index => $child) {
            $child['email'] = $this->incrementEmail($data['email'], $index + 1);
            $child['password'] = $data['password'];
            $child['phone_number'] = $data['phone_number'];
            $child['mobile_number'] = $data['mobile_number'];
            $child['address'] = $data['address'];
            $child['account_type'] = 'student';
            $childUser = $this->create($child);

            $student = Student::whereUserId($childUser->id)->first();
            $student->parent_id = $parentId;
            $student->save();
        }
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function registerUser(Request $request)
    {
        $validated = $this->validator($request->all())->validate();

        $user = $this->create($validated);

        if ($validated['account_type'] === 'parent') {
            $this->createAccountForChildren($request->children, $validated, $user->id);
        }

        return $this->login('api', $request);
    }

    protected function incrementEmail(string $email, int $childIndex): string
    {
        $emailComponents = explode("@", $email);
        $childEmail = $emailComponents[0] . "+$childIndex";
        $fullChildEmail = $childEmail . "@" . $emailComponents[1];

        return $fullChildEmail;
    }
}
