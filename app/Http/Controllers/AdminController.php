<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // Fetch all candidates
    public function getCandidates()
    {
        $candidates = Candidate::all();
        return response()->json(['data' => $candidates], 200);
    }

    // Add new candidate
    public function addCandidate(Request $request)
{
    $validator = Validator::make($request->all(), [
        'nama' => 'required|string|max:255',
        'role' => 'required|string',
        'jurusan' => 'required|string|max:255',
        'kelas' => 'required|string|max:50',
        'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:9048',
        'visi_misi' => 'required|string|max:5000',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
    }

    try {
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('uploads/candidates', 'public');
        }

        $candidate = Candidate::create([
            'nama' => $request->nama,
            'role' => $request->role,
            'jurusan' => $request->jurusan,
            'kelas' => $request->kelas,
            'foto' => $fotoPath,
            'visi_misi' => $request->visi_misi,
        ]);

        return response()->json(['message' => 'Candidate added successfully', 'data' => $candidate], 201);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error adding candidate', 'error' => $e->getMessage()], 500);
    }
}

    // Update existing candidate
    public function updateCandidate(Request $request, $id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            return response()->json(['message' => 'Candidate not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'nullable|string|max:255',
            'role' => 'nullable|string',
            'jurusan' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:50',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:9048',
            'visi_misi' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('foto')) {
            if ($candidate->foto) {
                Storage::disk('public')->delete($candidate->foto);
            }
            $fotoPath = $request->file('foto')->store('uploads/candidates', 'public');
            $candidate->foto = $fotoPath;
        }

        $candidate->update($request->except('foto'));

        return response()->json(['message' => 'Candidate updated successfully', 'data' => $candidate], 200);
    }

    // Delete a candidate
    public function deleteCandidate($id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            return response()->json(['message' => 'Candidate not found'], 404);
        }

        if ($candidate->foto) {
            Storage::disk('public')->delete($candidate->foto);
        }

        $candidate->delete();

        return response()->json(['message' => 'Candidate deleted successfully'], 200);
    }

}
