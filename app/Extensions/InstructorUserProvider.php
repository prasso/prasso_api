<?php

namespace App\Extensions;
 
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
 
class InstructorUserProvider extends ServiceProvider implements UserProvider 
{
  /**
   * The Instructor User Model
   */
  private $model;
 
  /**
   * Create a new Instructor user provider.
   *
   * @return \Illuminate\Contracts\Auth\Authenticatable|null
   * @return void
   */
  public function __construct(\Illuminate\Foundation\Application $app)
  {
    parent::__construct( $app);
     $this->model = $app->make("\App\Models\Instructor");
  }
 
  /**
   * Retrieve a user by the given credentials.
   *
   * @param  array  $credentials
   * @return \Illuminate\Contracts\Auth\Authenticatable|null
   */
  public function retrieveByCredentials(array $credentials)
  {
      if (empty($credentials)) {
          return;
      }
 
      $user = $this->model->fetchUserByCredentials(['email' => $credentials['username']]);
 
      return $user;
  }
  
  /**
   * Validate a user against the given credentials.
   *
   * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
   * @param  array  $credentials  Request credentials
   * @return bool
   */
  public function validateCredentials(Authenticatable $user, Array $credentials)
  {
      return ($credentials['username'] == $user->getAuthIdentifier() &&
    md5($credentials['password']) == $user->getAuthPassword());
  }
 
  public function retrieveById($identifier) {}
 
  public function retrieveByToken($identifier, $token) {}
 
  public function updateRememberToken(Authenticatable $user, $token) {}
}