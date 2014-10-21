<?php

namespace MV\ForumParserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Goutte\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class LoginController extends Controller
{
    /**
     * @var string
     */
    private $_url = 'http://m.mediavida.com/login.php';

    /**
     * @var Client
     */
    private $_client;

    function __construct()
    {
        $this->_client = new Client();
    }

    /**
     * Login via POST
     *   POST:
     *      username string
     *      password string
     *
     * @return JsonResponse
     */
    public function loginAction()
    {
        $data = array();
        $html = $this->_getHtml();
        $req = Request::createFromGlobals();
        $user = $req->request->get('username');
        $pass = $req->request->get('password');

        $form = $html->selectButton('Entrar')->form();
        $data = $this->_client->submit($form, array('name' => 'DarkSoldier', 'password' => 's0l0c0re', 'cookie' => 1));
        $cookie = $this->_client->getCookieJar();
        var_dump($data->html());
        return new JsonResponse((array) $cookie);
    }

    /**
     * Devuelve el crawler contenedor de la parte de login de MV
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function _getHtml()
    {
        return $this->_client->request('GET', $this->_url);
    }
}
