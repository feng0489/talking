<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2018/12/18
 * Time: 15:33
 */

namespace app\index\controller;


use think\Controller;

class Talking extends Controller
{

    public function index()
    {
        return $this->fetch("talking/talking");
    }

}