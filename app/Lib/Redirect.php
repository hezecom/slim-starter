<?php namespace App\Lib;
/**
 * Redirect
 *
 * @author    Hezekiah O. <support@hezecom.com>
 */
class Redirect
{
    protected $name;
    protected $status;

    public function __construct($name=null,$status =301)
    {
        $this->name = $name;
        $this->status = $status;
    }
    public function __destruct()
    {
        $this->redirect();
    }

    public function to($name,$status =301)
    {
        $this->name = $name;
        $this->status = $status;
        return $this;
    }

    public function route($name, $params1 =[], $params2=[],$status =301)
    {
        $this->name = route($name,$params1,$params2);
        $this->status = $status;
        return $this;
    }

    public function with($type,  $message)
    {
        flash($type, $message);
        return $this;
    }

    public function redirect(){
        if (headers_sent() === false)
        {
            header('Location: ' . $this->name, true, $this->status);
            exit;
        }
        exit('window.location.replace("'.$this->name.'");');
    }
}


