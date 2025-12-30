<?php

namespace App\Exports;

use App\Models\Book;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::all()->map(function ($user, $index) {
            return [
                'no' => $index + 1,
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'profile_photo_path' => $user->profile_photo_path,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'no',
            'id',
            'name',
            'username',
            'email',
            'phone',
            'address',
            'profile_photo_path'
        ];
    }
}
