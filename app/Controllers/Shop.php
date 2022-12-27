<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\KategoriModel;
use App\Models\KomentarModel;
use App\Libraries\Bantuan;
use App\Models\DiskonModel;

class Shop extends BaseController
{
    private $url = "https://api.rajaongkir.com/starter/";
    private $apiKey = "f484156da9817c8a4ff28f8d7b03144f";

    public function __construct()
    {
        helper('form');
        $this->kategori = new KategoriModel();
        $this->barang = new BarangModel();
        $this->komentar = new KomentarModel();
        $this->bantuan = new Bantuan();

        // new
        $this->diskon = new DiskonModel();
    }

    public function index()
    {
        $barang = $this->barang->select('barang.*, kategori.nama AS kategori')->join('kategori', 'barang.id_kategori=kategori.id')->findAll();
        $kategori = $this->kategori->findAll();

        return view('shop/index', [
            'barangs' => $barang,
            'kategoris' => $kategori,
        ]);
    }

    public function category()
    {
        $id = $this->request->uri->getSegment(3);


        $barang = $this->barang->select('barang.*, kategori.nama AS kategori')->where('id_kategori', $id)->join('kategori', 'barang.id_kategori=kategori.id')->where('id_kategori', $id)->findAll();
        $kategori = $this->kategori->findAll();
        return view('shop/index', [
            'barangs' => $barang,
            'kategoris' => $kategori,
        ]);
    }

    public function product()
    {
        $id = $this->request->uri->getSegment(3);

        $barang = $this->barang->find($id);
        $kategori = $this->kategori->findAll();
        $komentar = $this->komentar->select('komentar.*, user.username')->where('id_barang', $id)->join('user', 'komentar.id_user=user.id')->findAll();

        // new
        $diskon = $this->diskon->first();
        // end

        $provinsi = $this->bantuan->rajaongkir($this->url . "province", $this->apiKey, method: "GET");

        return view('shop/product', [
            'barang' => $barang,
            'kategoris' => $kategori,
            'komentars' => $komentar,
            'disc' => $diskon,
            'provinsi' => json_decode($provinsi)->rajaongkir->results,
        ]);
    }

    public function getCity()
    {
        if ($this->request->isAJAX()) {
            $id_province = $this->request->getGet('id_province');
            $send = ['province' => $id_province];
            $data = $this->bantuan->rajaongkir($this->url . "city?province=" . $id_province, $this->apiKey,  method: "GET");
            return $this->response->setJSON($data);
        }
    }

    public function getCost()
    {
        if ($this->request->isAJAX()) {
            $origin = $this->request->getGet('origin');
            $destination = $this->request->getGet('destination');
            $weight = $this->request->getGet('weight');
            $courier = $this->request->getGet('courier');
            $send = [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weight,
                'courier' => $courier,
            ];
            $data = $this->bantuan->rajaongkir($this->url . "cost", $this->apiKey, $send, "POST");
            return $this->response->setJSON($data);
        }
    }

    public function getdiskon()
    {
        $disc = $this->diskon->where(['aktif' => 1, 'tanggal_mulai_berlaku <=' => date("Y-m-d"), 'tanggal_akhir_berlaku >=' => date("Y-m-d")])->findAll();
        $kode = $this->request->getPost('kodeInput');

        if ($disc != null) {
            foreach ($disc as $diskon) {
                if ($kode == $diskon->kode_voucher) {
                    $hasil['data'] = $diskon;
                    $hasil['success'] =  true;
                    $hasil['error'] =  false;
                    return json_encode($hasil);
                }
            }
            $hasil['data'] = 'Voucher tidak tersedia!';
            $hasil['success'] =  false;
            $hasil['error'] =  true;
        } else {
            $hasil['data'] = 'Voucher kosong';
            $hasil['success'] =  false;
            $hasil['error'] =  true;
        }
        return json_encode($hasil);
    }
}
