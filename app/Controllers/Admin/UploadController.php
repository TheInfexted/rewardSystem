<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class UploadController extends BaseController
{
    public function image()
    {
        $img = $this->request->getFile('file'); // assuming frontend uploads file in "file"

        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move(WRITEPATH . 'uploads/landing', $newName);
            return $this->response->setJSON(['status' => 'ok', 'filename' => $newName]);
        }
    }

    public function remove()
    {
        $filename = $this->request->getPost('filename');
        $filePath = WRITEPATH . 'uploads/landing/' . $filename;

        if ($filename && is_file($filePath) && unlink($filePath)) {
            return $this->response->setJSON(['status' => 'ok', 'message' => 'Image removed']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Remove failed']);
    }
}
