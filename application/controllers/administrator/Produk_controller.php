<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produk_controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_admin_logged_in();

        // Load model
        $this->load->model('Produk_model', 'produk_model');
        $this->load->model('produk_kategori_model');
        $this->load->model('produk_gambar_model', 'produk_gambar_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $data['title'] = 'Daftar Produk';
        $data['list_produk'] = $this->produk_model->get_all();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar');
        $this->load->view('produk/index', $data);
        $this->load->view('templates/footer');
    }

    public function tambah_produk()
    {
        $data['title'] = 'Tambah Produk';

        $this->form_validation->set_rules(
            'nama_produk', 'Nama Produk', 'required'
        );
        $this->form_validation->set_rules(
            'kategori_produk', 'Kategori', 'required'
        );

        if ($this->form_validation->run() !== FALSE) {
            $this->__simpan_produk();
        } else {
            $data['list_kategori'] =
                $this->produk_kategori_model->get_all();

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar');
            $this->load->view(
                'produk/tambah_produk', $data
            );
            $this->load->view('templates/footer');
        }
    }

    private function __simpan_produk()
    {
        $data = [
            'nama' => ucwords($this->input->post('nama_produk')),
            'kategori_id' => $this->input->post('kategori_produk'),
            'harga' => $this->input->post('harga_produk'),
            'stok' => $this->input->post('stok_produk'),
            'deskripsi' => ucfirst(
                $this->input->post('deskripsi_produk')
            )
        ];

        $id_produk = $this->produk_model->tambah($data);

        if (!$id_produk) {
            $this->__pesan('danger', 'Gagal menambahkan produk!');
            redirect('admin/produk/tambah');
            return;
        }

        // Upload gambar jika tersedia
        if (
            isset($_FILES['gambar_produk']['name']) &&
            is_array($_FILES['gambar_produk']['name'])
        ) {
            $count = count($_FILES['gambar_produk']['name']);

            if ($count > 0) {
                $this->__produk_gambar_upload($count, $id_produk);
            }
        }

        $this->__pesan('success', 'Berhasil menambahkan produk!');
        redirect('admin/produk');
    }

    private function __produk_gambar_upload($count, $id_produk)
    {
        $folder = FCPATH . 'uploads/produk/';

        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        for ($i = 0; $i < $count; $i++) {
            if (empty($_FILES['gambar_produk']['name'][$i])) {
                continue;
            }

            $_FILES['file']['name'] =
                $_FILES['gambar_produk']['name'][$i];

            $_FILES['file']['type'] =
                $_FILES['gambar_produk']['type'][$i];

            $_FILES['file']['tmp_name'] =
                $_FILES['gambar_produk']['tmp_name'][$i];

            $_FILES['file']['error'] =
                $_FILES['gambar_produk']['error'][$i];

            $_FILES['file']['size'] =
                $_FILES['gambar_produk']['size'][$i];

            $config = [];
            $config['upload_path'] = $folder;
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 5000;
            $config['file_name'] =
                'produk-' . $id_produk . '-' . uniqid();

            $this->load->library('upload');
            $this->upload->initialize($config);

            if ($this->upload->do_upload('file')) {
                $uploadData = $this->upload->data();

                $gambar = [
                    'nama_gambar' => $uploadData['file_name'],
                    'produk_id' => $id_produk
                ];

                $this->produk_gambar_model->tambah($gambar);
            } else {
                log_message(
                    'error',
                    $this->upload->display_errors('', '')
                );
            }
        }
    }

    public function ubah_produk($id)
    {
        $produk = $this->produk_model->get_by_id($id);

        if (!$produk) {
            $this->__pesan('danger', 'Produk tidak ditemukan!');
            redirect('admin/produk');
            return;
        }

        $this->form_validation->set_rules(
            'nama_produk', 'Nama produk', 'required'
        );
        $this->form_validation->set_rules(
            'kategori_produk', 'Kategori', 'required'
        );

        if ($this->form_validation->run() !== FALSE) {
            $this->__ubah_produk($id);
        } else {
            $data['title'] = 'Ubah Produk';
            $data['produk'] = $produk;
            $data['list_kategori'] =
                $this->produk_kategori_model->get_all();

            $data['gambar_model'] = $this->produk_gambar_model;

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar');
            $this->load->view('produk/ubah_produk', $data
            );
            $this->load->view('templates/footer');
        }
    }

    private function __ubah_produk($id)
    {
        $data = [
            'nama' => ucwords($this->input->post('nama_produk')),
            'kategori_id' => $this->input->post('kategori_produk'),
            'harga' => $this->input->post('harga_produk'),
            'stok' => $this->input->post('stok_produk'),
            'deskripsi' => ucfirst(
                $this->input->post('deskripsi_produk')
            )
        ];

        // Periksa apakah gambar baru dipilih
        $ada_gambar_baru = false;

        if (
            isset($_FILES['gambar_produk']['name']) &&
            is_array($_FILES['gambar_produk']['name'])
        ) {
            foreach ($_FILES['gambar_produk']['name'] as $nama) {
                if (!empty($nama)) {
                    $ada_gambar_baru = true;
                    break;
                }
            }
        }

        $folder = FCPATH . 'uploads/produk/';
        $gambar_baru = [];
        $gambar_lama = [];

        // Jika ada gambar baru, upload dahulu
        if ($ada_gambar_baru) {
            $hasil = $this->__upload_gambar_baru($id);

            if (!$hasil['status']) {
                $this->__pesan(
                    'danger',
                    'Gagal mengganti gambar: ' . $hasil['error']
                );

                redirect('admin/produk/ubah/' . $id);
                return;
            }

            $gambar_baru = $hasil['files'];

            // Ambil gambar lama sebelum mengganti
            $gambar_lama =
                $this->produk_gambar_model->get_by_produk_id($id);
        }

        // Transaksi database
        $this->db->trans_begin();

        // Ubah informasi produk
        $this->produk_model->ubah($data, $id);

        if ($ada_gambar_baru) {

            // Tambah data gambar baru
            foreach ($gambar_baru as $nama_file) {
                $gambar = [
                    'nama_gambar' => $nama_file,
                    'produk_id' => $id
                ];

                $this->produk_gambar_model->tambah($gambar);
            }

            // Hapus data gambar lama
            foreach ($gambar_lama as $gambar) {
                $this->produk_gambar_model->hapus(
                    $gambar['id_gambar']
                );
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            // Bersihkan file baru jika database gagal
            foreach ($gambar_baru as $nama_file) {
                $path = $folder . $nama_file;

                if (is_file($path)) {
                    unlink($path);
                }
            }

            $this->__pesan(
                'danger',
                'Gagal mengubah data produk!'
            );

            redirect('admin/produk/ubah/' . $id);
            return;
        }

        $this->db->trans_commit();

        // Hapus file lama setelah database berhasil diperbarui
        if ($ada_gambar_baru) {
            foreach ($gambar_lama as $gambar) {
                $nama_file = basename($gambar['nama_gambar']);
                $path = $folder . $nama_file;

                if (is_file($path)) {
                    unlink($path);
                }
            }

            $this->__pesan(
                'success',
                'Berhasil mengubah produk dan mengganti gambar!'
            );
        } else {
            $this->__pesan(
                'success',
                'Berhasil mengubah produk!'
            );
        }

        redirect('admin/produk');
    }

    // Fungsi upload gambar baru untuk proses ubah produk
    private function __upload_gambar_baru($id_produk)
    {
        $folder = FCPATH . 'uploads/produk/';

        // Pastikan folder upload tersedia dan dapat ditulisi.
        if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
            return [
                'status' => false,
                'files' => [],
                'error' => 'Folder uploads/produk tidak dapat dibuat.'
            ];
        }

        if (!is_writable($folder)) {
            return [
                'status' => false,
                'files' => [],
                'error' => 'Folder uploads/produk tidak dapat ditulisi.'
            ];
        }

        if (
            !isset($_FILES['gambar_produk']['name']) ||
            !is_array($_FILES['gambar_produk']['name'])
        ) {
            return [
                'status' => false,
                'files' => [],
                'error' => 'Data gambar tidak ditemukan.'
            ];
        }

        $gambar_baru = [];
        $jumlah = count($_FILES['gambar_produk']['name']);

        for ($i = 0; $i < $jumlah; $i++) {
            $nama_asli = $_FILES['gambar_produk']['name'][$i];

            // Lewati input file yang kosong.
            if ($nama_asli === '') {
                continue;
            }

            $kode_error = $_FILES['gambar_produk']['error'][$i];
            if ($kode_error !== UPLOAD_ERR_OK) {
                $pesan_error = [
                    UPLOAD_ERR_INI_SIZE => 'Ukuran gambar melebihi batas upload PHP.',
                    UPLOAD_ERR_FORM_SIZE => 'Ukuran gambar melebihi batas form.',
                    UPLOAD_ERR_PARTIAL => 'Gambar hanya terunggah sebagian.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara upload tidak tersedia.',
                    UPLOAD_ERR_CANT_WRITE => 'Gambar gagal ditulis ke penyimpanan.',
                    UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh konfigurasi PHP.'
                ];

                $error = $pesan_error[$kode_error] ?? 'Terjadi kesalahan saat mengunggah gambar.';
                $this->__hapus_file_upload_sementara($folder, $gambar_baru);

                return [
                    'status' => false,
                    'files' => [],
                    'error' => $error
                ];
            }

            $_FILES['file'] = [
                'name' => $nama_asli,
                'type' => $_FILES['gambar_produk']['type'][$i],
                'tmp_name' => $_FILES['gambar_produk']['tmp_name'][$i],
                'error' => $kode_error,
                'size' => $_FILES['gambar_produk']['size'][$i]
            ];

            $config = [
                'upload_path' => $folder,
                'allowed_types' => 'jpg|jpeg|png|gif',
                'max_size' => 5120,
                'file_name' => 'produk-' . $id_produk . '-' . uniqid(),
                'remove_spaces' => true,
                'detect_mime' => true,
                'mod_mime_fix' => true
            ];

            $this->load->library('upload');
            // TRUE mereset konfigurasi upload sebelumnya untuk setiap file.
            $this->upload->initialize($config, true);

            if (!$this->upload->do_upload('file')) {
                $error = strip_tags($this->upload->display_errors('', ''));
                $this->__hapus_file_upload_sementara($folder, $gambar_baru);

                return [
                    'status' => false,
                    'files' => [],
                    'error' => $error
                ];
            }

            $upload_data = $this->upload->data();
            $gambar_baru[] = $upload_data['file_name'];
        }

        if (empty($gambar_baru)) {
            return [
                'status' => false,
                'files' => [],
                'error' => 'Tidak ada gambar baru yang berhasil diproses. Pastikan gambar sudah dipilih.'
            ];
        }

        return [
            'status' => true,
            'files' => $gambar_baru,
            'error' => ''
        ];
    }

    // Menghapus file baru jika salah satu upload gagal.
    private function __hapus_file_upload_sementara($folder, $daftar_file)
    {
        foreach ($daftar_file as $nama_file) {
            $path = $folder . basename($nama_file);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function hapus_produk($id)
    {
        $produk = $this->produk_model->get_by_id($id);

        if (!$produk) {
            $this->__pesan('danger', 'Produk tidak ditemukan!');
            redirect('admin/produk');
            return;
        }

        // Ambil gambar produk
        $list_gambar =
            $this->produk_gambar_model->get_by_produk_id($id);

        // Hapus data dalam database
        $this->db->trans_begin();

        foreach ($list_gambar as $gambar) {
            $this->produk_gambar_model->hapus(
                $gambar['id_gambar']
            );
        }

        $this->produk_model->hapus($id);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $this->__pesan(
                'danger',
                'Gagal menghapus produk!'
            );
        } else {
            $this->db->trans_commit();

            // Hapus file gambar
            foreach ($list_gambar as $gambar) {
                $path = FCPATH . 'uploads/produk/' .
                    basename($gambar['nama_gambar']);

                if (is_file($path)) {
                    unlink($path);
                }
            }

            $this->__pesan(
                'success',
                'Berhasil menghapus produk!'
            );
        }

        redirect('admin/produk');
    }

    // Pesan notifikasi
    private function __pesan($jenis, $pesan)
    {
        $jenis_diizinkan = [
            'success', 'danger', 'warning', 'info'
        ];

        if (!in_array($jenis, $jenis_diizinkan)) {
            $jenis = 'info';
        }

        $html = '
            <div class="alert alert-' . $jenis . ' alert-dismissible fade show"
                 role="alert">
                ' . html_escape($pesan) . '
                <button type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        ';

        $this->session->set_flashdata('message', $html);
    }
}