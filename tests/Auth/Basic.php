<?php

class RequestsTest_Auth_Basic extends RequestsTestCase {
	public static function transportProvider() {
		return array(
			array('Requests_Transport_fsockopen'),
			array('Requests_Transport_cURL'),
		);
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testUsingArray($transport) {
		if (!call_user_func(array($transport, 'test'))) {
			$this->markTestSkipped($transport . ' is not available');
			return;
		}

		$options = array(
			'auth'      => array('user', 'passwd'),
			'transport' => $transport,
		);
		$request = Requests::get(httpbin('/basic-auth/user/passwd'), array(), $options);
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body);
		$this->assertTrue($result->authenticated);
		$this->assertSame('user', $result->user);
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testUsingInstantiation($transport) {
		if (!call_user_func(array($transport, 'test'))) {
			$this->markTestSkipped($transport . ' is not available');
			return;
		}

		$options = array(
			'auth'      => new Requests_Auth_Basic(array('user', 'passwd')),
			'transport' => $transport,
		);
		$request = Requests::get(httpbin('/basic-auth/user/passwd'), array(), $options);
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body);
		$this->assertTrue($result->authenticated);
		$this->assertSame('user', $result->user);
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testPOSTUsingInstantiation($transport) {
		if (!call_user_func(array($transport, 'test'))) {
			$this->markTestSkipped($transport . ' is not available');
			return;
		}

		$options = array(
			'auth'      => new Requests_Auth_Basic(array('user', 'passwd')),
			'transport' => $transport,
		);
		$data    = 'test';
		$request = Requests::post(httpbin('/post'), array(), $data, $options);
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body);

		$auth = $result->headers->Authorization;
		$auth = explode(' ', $auth);

		$this->assertSame(base64_encode('user:passwd'), $auth[1]);
		$this->assertSame('test', $result->data);
	}

	public function testMissingPassword() {
		$this->setExpectedException('Requests_Exception', 'Invalid number of arguments');
		new Requests_Auth_Basic(array('user'));
	}

}
