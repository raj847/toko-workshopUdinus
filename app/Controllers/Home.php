<?php

namespace App\Controllers;

use App\Models\DiskonModel;

class Home extends BaseController
{

    public function __construct()
    {
        $this->diskon = new DiskonModel();
    }

    public function index()
    {
        $diskon = $this->diskon->where(['aktif' => 1, 'tanggal_mulai_berlaku <=' => date("Y-m-d"), 'tanggal_akhir_berlaku >=' => date("Y-m-d")])->first();

        return view('home', [
            'disc' => $diskon
        ]);
    }

    public function contact()
    {
        return view('contact');
    }
}
