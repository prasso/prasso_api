<?php

namespace Tests\Unit;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Illuminate\Validation\ValidationException;

class CreateNewUserTest extends TestCase
{
    public function test_create_new_user()
    {
        $user = User::where('email','johndoe@example.com')->first();
        if ($user){$user->delete();}
        $pass = Hash::make('123456789');
        // Create a mock input array
        $input = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => $pass,
            'password_confirmation' => $pass,
            'phone' => '12312311',
            'version' => 'v1'
        ];

        // Call the create method on the CreateNewUser action
        $action = new CreateNewUser();
        try {
            $result = $action->create($input);
        } catch (ValidationException $e) {
            info( $e->errors());
        }
        

        // Assert that the result is a User object with the correct name and email
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($input['name'], $result->name);
        $this->assertEquals($input['email'], $result->email);
        $this->assertTrue(Hash::check($input['password'], $result->password));
    }

}