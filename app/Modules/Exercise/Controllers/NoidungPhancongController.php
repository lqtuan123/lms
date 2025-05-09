<?php

namespace App\Modules\Exercise\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Exercise\Models\BoDeTuLuan;
use App\Modules\Exercise\Models\BoDeTracNghiem;
use App\Modules\Exercise\Models\NoidungPhancong;
use App\Modules\Resource\Models\Resource;
use App\Models\Tag;
use App\Modules\Exercise\Models\Phancong;
use App\Modules\Teaching_2\Models\PhanCong as ModelsPhanCong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NoidungPhancongController extends Controller
{
    public function index()
    {
        $noidungPhancongs = NoidungPhancong::all();

        foreach ($noidungPhancongs as $noidung) {
            $resourceIds = json_decode($noidung->resources, true)['resource_ids'] ?? [];
            $resourceUrls = Resource::whereIn('id', $resourceIds)->pluck('url')->toArray();
            $noidung->setAttribute('resource_urls', $resourceUrls);

            $tagIds = DB::table('tag_noidungphancong')->where('noidungphancong_id', $noidung->id)->pluck('tag_id')->toArray();
            $tags = Tag::whereIn('id', $tagIds)->pluck('title')->toArray();
            $noidung->setAttribute('tags', $tags);
        }

        return view('Exercise::admin.noidung_phancong.index', [
            'noidungPhancongs' => $noidungPhancongs,
            'active_menu' => 'noidungphancong'
        ]);
    }

    public function create()
{
    return view('Exercise::noidung_phancong.create', [
        'breadcrumb' => '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item"><a href="' . route('admin.noidung_phancong.index') . '">Nội dung phân công</a></li>
            <li class="breadcrumb-item active">Tạo nội dung phân công</li>',
        'active_menu' => 'noidungphancong_add',
        'tags' => Tag::where('status', 'active')->orderBy('title', 'ASC')->get(),
        'phancongs' => ModelsPhanCong::all(),
        'bode_tuluans' => BoDeTuLuan::all(),  // Đổi tên biến cho đúng
        'bode_tracnghiems' => BoDeTracNghiem::all(), // Thêm nếu muốn hiển thị luôn Trắc nghiệm
    ]);
}


    public function store(Request $request)
    {
        

        $validated = $request->validate([
            'phancong_id' => 'required|exists:phancong,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:noidung_phancong,slug',
            'content' => 'nullable|string',
            'time_limit' => 'nullable|integer',
            'tuluan' => 'nullable|array',
            'tracnghiem' => 'nullable|array',
            'tag_ids' => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,mp4,mp3,pdf,doc,mov,docx,ppt,pptx,xls,xlsx|max:204800',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);

        $noidungPhancong = NoidungPhancong::create($validated);

        // Lưu tài liệu (nếu có)
        $resourceIds = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $resource = Resource::createResource($request, $file, 'Event');
                $resourceIds[] = $resource->id;
            }
        }

        // Cập nhật tài liệu vào Event
        $noidungPhancong->resources = json_encode([
            'noidungphancong_id' => $noidungPhancong->id,
            'resource_ids' => $resourceIds,
        ]);
        $noidungPhancong->save();
        // 🔹 Lưu tự luận
        if ($request->has('tuluan') && !empty($request->tuluan)) {
            $noidungPhancong->update([
                'tuluan' => json_encode([
                    'noidungphancong_id' => $noidungPhancong->id,
                    'bodetuluan_ids' => array_values($request->tuluan)
                ])
            ]);
        }

        // 🔹 Lưu trắc nghiệm
        if ($request->has('tracnghiem') && !empty($request->tracnghiem)) {
            $noidungPhancong->update([
                'tracnghiem' => json_encode([
                    'noidungphancong_id' => $noidungPhancong->id,
                    'bodetracnghiem_ids' => array_values($request->tracnghiem)
                ])
            ]);
        }
        


        // Lưu tag
        if ($request->has('tag_ids')) {
            (new \App\Http\Controllers\TagController())->store_noidungphancong_tag($noidungPhancong->id, $request->tag_ids);
        }


        return redirect()->route('admin.noidung_phancong.index')->with('success', 'Nội dung phân công đã được tạo thành công!');
    }

    public function edit($id)
    {
        return view('Exercise::admin.noidung_phancong.edit', [
            'noidungPhancong' => NoidungPhancong::findOrFail($id),
            'breadcrumb' => '
                <li class="breadcrumb-item"><a href="#">/</a></li>
                <li class="breadcrumb-item"><a href="' . route('noidung_phancong.index') . '">Nội dung phân công</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa nội dung phân công</li>',
            'active_menu' => 'noidungphancong',
            'tags' => Tag::where('status', 'active')->orderBy('title', 'ASC')->get(),
            'tag_ids' => DB::table('tag_noidungphancong')->where('noidungphancong_id', $id)->pluck('tag_id')->toArray(),
            'phancongs' => ModelsPhanCong::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        $noidungPhancong = NoidungPhancong::findOrFail($id);

        $validated = $request->validate([
            'phancong_id' => 'sometimes|exists:phancong,id',
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:noidung_phancong,slug,' . $id,
            'content' => 'nullable|string',
            'time_limit' => 'nullable|integer',
            'tag_ids' => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,mp4,mp3,pdf,doc,mov,docx,ppt,pptx,xls,xlsx|max:204800',
            'tuluan' => 'nullable|array',
            'trachnghiem' => 'nullable|array',
        ]);

        $noidungPhancong->update($validated);

        // Cập nhật tài nguyên
        if ($request->hasFile('documents')) {
            $resourceIds = [];
            foreach ($request->file('documents') as $file) {
                $resource = Resource::createResource($request, $file, 'NoidungPhancong');
                $resourceIds[] = $resource->id;
            }
            $noidungPhancong->update(['resources' => json_encode(['resource_ids' => $resourceIds])]);
        }
         // 🔹 Cập nhật tự luận
        if ($request->has('tuluan') && !empty($request->tuluan)) {
            $noidungPhancong->update([
                'tuluan' => json_encode([
                    'noidungphancong_id' => $noidungPhancong->id,
                    'bodetuluan_ids' => array_values($request->tuluan)
                ])
            ]);
        } else {
            $noidungPhancong->update(['tuluan' => null]); // Nếu bỏ chọn thì xóa tự luận
        }

        // 🔹 Cập nhật trắc nghiệm
        if ($request->has('trachnghiem') && !empty($request->tracnghiem)) {
            $noidungPhancong->update([
                'tracnghiem' => json_encode([
                    'noidungphancong_id' => $noidungPhancong->id,
                    'bodetracnghiem_ids' => array_values($request->tracnghiem)
                ])
            ]);
        } else {
            $noidungPhancong->update(['trachnghiem' => null]); // Nếu bỏ chọn thì xóa trắc nghiệm
        }

        // Cập nhật tags
        DB::table('tag_noidungphancong')->where('noidungphancong_id', $id)->delete();
        if ($request->has('tag_ids')) {
            DB::table('tag_noidungphancong')->insert(
                collect($request->tag_ids)->map(fn($tagId) => [
                    'noidungphancong_id' => $id,
                    'tag_id' => $tagId
                ])->toArray()
            );
        }

        return redirect()->route('admin.noidung_phancong.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy($id)
    {
        $noidungPhancong = NoidungPhancong::findOrFail($id);
        DB::table('tag_noidungphancong')->where('noidungphancong_id', $id)->delete();
        $noidungPhancong->delete();

        return redirect()->route('admin.noidung_phancong.index')->with('success', 'Xóa thành công!');
    }
}
