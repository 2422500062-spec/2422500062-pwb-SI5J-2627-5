<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_dashboard_controller extends CI_Controller
{
    public function index()
    {
        $data['title'] = 'Afdila Petshop';

        $this->load->view('administrator/templates/header', $data);
        $this->load->view('administrator/templates/sidebar', $data);
        $this->load->view('administrator/dashboard', $data);
        $this->load->view('administrator/templates/footer', $data);
    }
}
