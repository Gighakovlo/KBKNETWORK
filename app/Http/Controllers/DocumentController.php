<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DocumentController extends Controller
{
    // 1. Tampilkan Halaman Arsip Dokumen
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $query = Document::query();

        if (!empty($search)) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->query());

        return view('inventory.documents.index', compact('documents', 'search', 'perPage'));
    }

    // 2. Simpan Dokumen Baru
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'document_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar|max:10240', // Max 10MB
        ]);

        $file = $request->file('document_file');
        $filename = time() . '_' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $file->getClientOriginalName());

        // Simpan ke folder public/uploads/documents
        $file->move(public_path('uploads/documents'), $filename);

        Document::create([
            'title' => $request->title,
            'category' => $request->category ?? 'Umum',
            'file_path' => 'uploads/documents/' . $filename,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil diarsipkan!');
    }

    // 3. Update Dokumen
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar|max:10240',
        ]);

        $doc = Document::findOrFail($id);
        $data = [
            'title' => $request->title,
            'category' => $request->category ?? 'Umum',
            'description' => $request->description,
        ];

        // Jika ada file baru yang diupload, ganti yang lama
        if ($request->hasFile('document_file')) {
            if (File::exists(public_path($doc->file_path))) {
                File::delete(public_path($doc->file_path));
            }

            $file = $request->file('document_file');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/documents'), $filename);
            $data['file_path'] = 'uploads/documents/' . $filename;
        }

        $doc->update($data);
        return redirect()->back()->with('success', 'Detail dokumen berhasil diperbarui!');
    }

    // 4. Hapus Satu Dokumen
    public function destroy($id)
    {
        $doc = Document::findOrFail($id);
        if (File::exists(public_path($doc->file_path))) {
            File::delete(public_path($doc->file_path));
        }
        $doc->delete();
        return response()->json(['success' => true, 'message' => 'Dokumen berhasil dihapus permanen.']);
    }

    // 5. Hapus Massal (Bulk Delete)
    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);

        $docs = Document::whereIn('id', $request->ids)->get();
        foreach ($docs as $doc) {
            if (File::exists(public_path($doc->file_path))) {
                File::delete(public_path($doc->file_path));
            }
            $doc->delete();
        }

        return response()->json(['success' => true, 'message' => count($request->ids) . ' dokumen berhasil dimusnahkan.']);
    }
}