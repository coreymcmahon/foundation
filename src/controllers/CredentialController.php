<?php namespace Orchestra\Foundation;

use Auth,
	Event,
	Input,
	Redirect,
	Session,
	View,
	Orchestra\App,
	Orchestra\Messages,
	Orchestra\Model\User,
	Orchestra\Site;

class CredentialController extends AdminController {

	/**
	 * Define the filters.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->beforeFilter('orchestra.logged', array(
			'only' => array(
				'getLogin', 'postLogin', 
				'getRegister', 'postRegister',
			),
		));

		$this->beforeFilter('orchestra.registrable', array(
			'only' => array(
				'getRegister', 'postRegister',
			),
		));

		$this->beforeFilter('orchestra.csrf', array(
			'only' => array(
				'postLogin', 'postRegister',
			),
		));
	}

	/**
	 * Login Page
	 *
	 * GET (:orchestra)/login
	 *
	 * @access public
	 * @return Response
	 */
	public function getLogin()
	{
		Site::set('title', trans("orchestra/foundation::title.login"));

		return View::make('orchestra/foundation::credential.login')
			->with('redirect', Session::get('orchestra.redirect', handles('orchestra/foundation::/')));
	}

	/**
	 * POST Login
	 *
	 * POST (:orchestra)/login
	 *
	 * @access public
	 * @return Response
	 */
	public function postLogin()
	{
		$input      = Input::all();
		$validation = App::make('Orchestra\Services\Validation\Auth')->with($input);

		// Validate user login, if any errors is found redirect it back to
		// login page with the errors.
		if ($validation->fails())
		{
			return Redirect::to(handles('orchestra/foundation::login'))
					->withInput()
					->withErrors($validation);
		}

		if ($this->authenticate($input))
		{
			Messages::add('success', trans('orchestra/foundation::response.credential.logged-in'));
			return Redirect::to(Input::get('redirect', handles('orchestra/foundation::/')));
		}

		Messages::add('error', trans('orchestra/foundation::response.credential.invalid-combination'));
		return Redirect::to(handles('orchestra/foundation::login'));
	}

	/**
	 * Logout the user
	 *
	 * DELETE (:bundle)/login
	 *
	 * @access public
	 * @return Response
	 */
	public function deleteLogin()
	{
		Event::fire('orchestra.auth: logout');

		Auth::logout();

		Messages::add('success', trans('orchestra/foundation::response.credential.logged-out'));

		return Redirect::to(Input::get('redirect', handles('orchestra/foundation::login')));
	}

	/**
	 * Authenticate the user.
	 *
	 * @access protected
	 * @param  array    $input
	 * @return boolean
	 */
	protected function authenticate($input)
	{
		$data = array(
			'email'    => $input['username'],
			'password' => $input['password'],
		);

		$remember = (isset($input['remember']) and $input['remember'] === 'yes');

		// We should now attempt to login the user using Auth class.
		if ( ! Auth::attempt($data, $remember)) return false;
		
		$user = Auth::user();

		// Verify the user account if has not been verified.
		if ((int) $user->status === User::UNVERIFIED)
		{
			$user->status = User::VERIFIED;
			$user->save();
		}

		Event::fire('orchestra.auth: login');
		return true;
	}
}