<?php
// app/Services/Auth/JsonGuard.php
namespace App\Extensions;
 
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use App\Extensions\SuperAdminUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
 
class SuperAdminAuthGuard implements Guard
{
  protected $request;
  protected $provider;
  protected $user;
 
  /**
   * Create a new authentication guard.
   *
   * @param  App\Extensions\SuperAdminUserProvider  $provider
   * @param  \Illuminate\Http\Request  $request
   * @return void
   */
  public function __construct(SuperAdminUserProvider $provider, Request $request)
  {
    $this->request = $request;
    $this->provider = $provider;
    $this->user = NULL;
  }
 
  /**
   * Determine if the current user is authenticated.
   *
   * @return bool
   */
  public function check()
  {
    return ! is_null($this->user());
  }
 
  /**
   * Determine if the current user is a guest.
   *
   * @return bool
   */
  public function guest()
  {
    return ! $this->check();
  }
 
  /**
   * Get the currently authenticated user.
   *
   * @return \Illuminate\Contracts\Auth\Authenticatable|null
   */
  public function user()
  {
    if (! is_null($this->user)) {
      return $this->user;
    }
  }
     
  /**
   * Get the ID for the currently authenticated user.
   *
   * @return string|null
  */
  public function id()
  {
    if ($user = $this->user()) {
      return $this->user()->getAuthIdentifier();
    }
  }
 
  /**
   * Validate a user's credentials.
   *
   * @return bool
   */
  public function validate(Array $credentials=[])
  {
//Log::info('SuperAdminAuthGuard validate: '.json_encode($credentials));
    if (empty($credentials['username']) || empty($credentials['password'])) {
      return false;
    }
 
    $user = $this->provider->retrieveByCredentials($credentials);
       
    if (! is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
      $this->setUser($user);
 
      return true;
    } else {
      return false;
    }
  }
 
  /**
   * Set the current user.
   *
   * @param  Array $user User info
   * @return void
   */
  public function setUser(Authenticatable $user)
  {
    $this->user = $user;
    return $this;
  }
}