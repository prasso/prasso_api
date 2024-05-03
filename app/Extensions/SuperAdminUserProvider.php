<?php

namespace App\Extensions;
 
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
 
class SuperAdminUserProvider extends ServiceProvider implements UserProvider 
{
  /**
   * The SiteAdmin User Model
   */
  private $model;
 
  /**
   * Create a new SiteAdmin user provider.
   *
   * @return \Illuminate\Contracts\Auth\Authenticatable|null
   * @return void
   */
  public function __construct(\Illuminate\Foundation\Application $app)
  {
    parent::__construct( $app);
     $this->model = $app->make("\App\Models\SuperAdmin");
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

  /**
     * Rehash the user's password if necessary.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $password
     * @return bool
     */
    public function rehashPasswordIfRequired(UserContract $user, array $credentials, bool $force = false)
    {
        // Implement your rehash logic here if necessary
        return false; // Return true if rehashing is successful, false otherwise
    }
}