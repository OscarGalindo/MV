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
        $user = strtolower($req->request->get('username'));
        $pass = $req->request->get('password');

        $form = $html->selectButton('Entrar')->form();
        $data_crawler = $this->_client->submit($form, array('name' => $user, 'password' => $pass, 'cookie' => 1));
        
        $data['logout_url'] = $data_crawler->filter('span.separator > a')->link()->getUri();
        $data['result'] = (strtolower($data_crawler->filter('#userinfo span')->eq(0)->text()) == $user) ? true : false;
        return new JsonResponse($data);
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
