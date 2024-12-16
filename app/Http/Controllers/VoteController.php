<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vote;
use Illuminate\Support\Facades\Validator;
use App\Models\Candidate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VoteController extends Controller
{
    // Voting oleh user (guru atau murid)
    public function vote(Request $request)
    {
        try {
            // Verifikasi login user (guru atau murid)
            $user = Auth::user(); // Menggunakan Sanctum untuk autentikasi

            if (!$user) {
                Log::warning('User not authenticated', ['request' => $request->all()]);
                return response()->json(['message' => 'User not authenticated.'], 401);
            }

            // Validasi input
            $validator = Validator::make($request->all(), [
                'candidate_id' => 'required|exists:candidates,id',
                'role' => 'required|in:ketua,wakil', // Role harus sesuai dengan pilihan
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
            }

            // Cek apakah user sudah melakukan voting untuk role ini
            $existingVote = Vote::where('user_id', $user->id)
                ->where('role', $request->role)
                ->first();

            if ($existingVote) {
                return response()->json(['message' => 'You have already voted for this role.'], 400);
            }

            // Simpan vote
            $vote = Vote::create([
                'nama_user' => $user->nama,
                'candidate_id' => $request->candidate_id,
                'role' => $request->role,
                'user_id' => $user->id, // Pastikan user_id disimpan
            ]);

            return response()->json(['message' => 'Vote submitted successfully', 'data' => $vote], 201);
        } catch (\Exception $e) {
            // Log error dan tampilkan pesan error
            Log::error('Error in voting process: ', ['error' => $e->getMessage(), 'trace' => $e->getTrace()]);

            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }
    public function resultVote()
{
    try {
        // Ambil semua kandidat beserta jumlah vote
        $results = Candidate::with(['votes' => function ($query) {
            $query->select('role', 'candidate_id');
        }])->withCount('votes')->get();

        // Grupkan berdasarkan role
        $groupedResults = $results->groupBy(function ($candidate) {
            return $candidate->votes->first()->role ?? 'Unknown';
        })->map(function ($group) {
            return $group->map(function ($candidate) {
                return [
                    'candidate_name' => $candidate->nama,
                    'vote_count' => $candidate->votes_count,
                ];
            });
        });

        return response()->json([
            'message' => 'Voting results retrieved successfully.',
            'data' => $groupedResults,
        ], 200);
    } catch (\Exception $e) {
        // error('Error retrieving voting results: ' . $e->getMessage());
        return response()->json([
            'message' => 'Internal Server Error',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
