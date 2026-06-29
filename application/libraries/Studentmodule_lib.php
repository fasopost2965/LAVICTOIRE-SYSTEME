<?php

defined('BASEPATH') or exit('No direct script access allowed');
class StudentModule_lib
{

    private $CI;
    private $allModules = array();
    protected $modules;
    public $perm_category;
    protected $student;

    public function __construct()
    {
        $this->CI      = &get_instance();
        $this->modules = array();
        $this->CI->load->library('session');
        self::loadModule(); //Initiate the userroles
    }

    public function loadModule()
    {
        if ($this->CI->session->has_userdata('student')) {
            $this->student = $this->CI->session->userdata('student');

            // Defensive check: Ensure 'role' key exists to avoid "Undefined array key" warning/error
            if (isset($this->student['role'])) {
                $this->allModules = $this->CI->Module_model->get_userpermission($this->student['role']);
                $role_name        = $this->student['role'];
                if (!empty($this->allModules)) {
                    foreach ($this->allModules as $mod_key => $mod_value) {

                        if (array_key_exists($role_name, (array)$mod_value)) {

                            if ($mod_value->{$role_name} == 1) {
                                $this->modules[$mod_value->short_code] = true;
                            } else {
                                $this->modules[$mod_value->short_code] = false;
                            }

                        }else{
                             $this->modules[$mod_value->short_code] = false;
                        }
                    }
                }
            } else {
                // Fallback or log if role is missing
                $this->modules = array();
            }
        }
    }

    public function hasActive($module = null)
    {
        if (isset($this->modules[$module]) && $this->modules[$module]) {
            return true;
        }

        return false;
    }

}
