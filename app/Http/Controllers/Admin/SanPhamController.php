<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SanPhamRequest;
use App\Models\DanhMuc;
use App\Models\HinhAnhSanPham;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SanPhamController extends Controller
{
    public function index()
    {
        $title = "Thông tin Sản phẩm";
        $listSanPham = SanPham::orderByDesc('is_type')->get();
        return view("admins.sanphams.index", compact("title", "listSanPham"));
    }
    public function create()
    {
        $title = "Thêm sản phẩm";
        $listDanhMuc = DanhMuc::query()->get();
        return view("admins.sanphams.create", compact("title", "listDanhMuc"));
    }

    public function store(SanPhamRequest $request)
    {
        if ($request->isMethod('POST')) {
            $params = $request->except('_token');
            // chuyen doi gia tri check bõ thanh booleam

            $params['is_new'] = $request->has('is_new') ? 1 : 0;
            $params['is_hot'] = $request->has('is_hot') ? 1 : 0;
            $params['is_hot_deal'] = $request->has('is_hot_deal') ? 1 : 0;
            $params['is_show_home'] = $request->has('is_show_home') ? 1 : 0;

            // xoa anh trong storage
            if ($request->hasFile('hinh_anh')) {
                $params['hinh_anh'] = $request->file('hinh_anh')->store('uploads/sanpham', 'public');
            } else {
                $params['hinh_anh'] = null;
            }
            $sanPham = SanPham::create($params);
            // lay id san pham vua them de them dc album
            $sanPhamID = $sanPham->id;
            if ($request->hasFile('list_hinh_anh')) {
                foreach ($request->file('list_hinh_anh') as $image) {
                    if ($image) {
                        $path = $image->store('uploads/hinhanhsanpham/id_' . $sanPhamID, 'public');
                        $sanPham->hinhAnhSanPham()->create([
                            'san_pham_id' => $sanPhamID,
                            'hinh_anh' => $path,
                        ]);
                    }
                }
            }
            return redirect()->route('admins.sanphams.index')->with('success', 'Thêm sản phâmr thành công');
        }
    }

    public function edit(string $id)
    {
        $title = "Cập nhật thông tin sản phẩm";
        $listDanhMuc = DanhMuc::query()->get();
        $sanPham = SanPham::query()->findOrFail($id);
        return view("admins.sanphams.edit", compact("title", "listDanhMuc", "sanPham"));
    }

    public function update(Request $request, string $id)
    {
        if ($request->isMethod('PUT')) {
            $params = $request->except('_token', '_method');
            // chuyen doi gia tri check bõ thanh booleam
            $params['is_new'] = $request->has('is_new') ? 1 : 0;
            $params['is_hot'] = $request->has('is_hot') ? 1 : 0;
            $params['is_hot_deal'] = $request->has('is_hot_deal') ? 1 : 0;
            $params['is_show_home'] = $request->has('is_show_home') ? 1 : 0;

            $sanPham = SanPham::query()->findOrFail($id);


            if ($request->hasFile('hinh_anh')) {
                if ($sanPham->hinh_anh && Storage::disk('public')->exists($sanPham->hinh_anh)) {
                    Storage::disk('public')->delete($sanPham->hinh_anh);
                }
                $params['hinh_anh'] = $request->file('hinh_anh')->store('uploads/sanpham', 'public');
            } else {
                $params['hinh_anh'] = $sanPham->hinh_anh;
            }
            // xu ly album

            $currentImage = $sanPham->hinhAnhSanPham->pluck('id')->toArray();
            $arrayCombine = array_combine($currentImage, $currentImage);
            $listHinhAnh = $request->list_hinh_anh ?? [];
            foreach ($sanPham->hinhAnhSanPham as $hinhAnhSanPham) {
                if (!array_key_exists($hinhAnhSanPham->id, $listHinhAnh)) {
                    if (Storage::disk('public')->exists($hinhAnhSanPham->hinh_anh)) {
                        Storage::disk('public')->delete($hinhAnhSanPham->hinh_anh);
                    }
                    $hinhAnhSanPham->delete();
                }
            }

            // truong hop them hoac sua\
            $listHinhAnh = $request->list_hinh_anh ?? [];
            foreach ($listHinhAnh as $key => $image) {
                if (!array_key_exists($key, $arrayCombine)) {
                    if ($request->hasFile("list_hinh_anh.$key")) {
                        $path = $image->store('uploads/hinhanhsanpham/id_' . $id, 'public');
                        $sanPham->hinhAnhSanPham()->create([
                            'san_pham_id' => $id,
                            'hinh_anh' => $path,
                        ]);
                    }
                } elseif (is_file($image) && $request->hasFile("list_hinh_anh.$key")) {
                    // truong hop thay doi hinh anh
                    $hinhAnhSanPham = HinhAnhSanPham::query()->find($key);
                    if ($hinhAnhSanPham && Storage::disk('public')->exists($hinhAnhSanPham->hinh_anh)) {
                        Storage::disk('public')->delete($hinhAnhSanPham->hinh_anh);
                    }
                    $path = $image->store('uploads/hinhanhsanpham/id_' . $id, 'public');
                    $hinhAnhSanPham->update([
                        'hinh_anh' => $path,
                    ]);
                }
            }
            $sanPham->update($params);
            return redirect()->route('admins.sanphams.index')->with('success', 'Cập nhật thành công');
        }
    }


    public function destroy(string $id)
    {
        //
        $sanPham = SanPham::query()->findOrFail($id);
        if ($sanPham->hinh_anh && Storage::disk('public')->exists($sanPham->hinh_anh)) {
            Storage::disk('public')->delete($sanPham->hinh_anh);
        }

        // xoa album
        $sanPham->hinhAnhSanPham()->delete();
        // xoa toan bo hinh anh trong thu muc
        $path = 'uploads/hinhanhsanpham/id_' . $id;
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->deleteDirectory($path);
        }
        $sanPham->delete();
        return redirect()->route('admins.sanphams.index')->with('success', 'Xóa sản phẩm thành công');
    }
}
