<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('umkm') && $this->user()->umkm !== null;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.1'],
            'hours_until_deadline' => ['required', 'numeric', 'min:1', 'max:168'], // 1 jam - 7 hari
            'urgency' => ['required', 'string', 'in:rendah,sedang,tinggi'],
            'delivery_district' => ['required', 'string'],
            'delivery_address' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Pilih bahan baku yang ingin dipesan.',
            'quantity.required' => 'Masukkan jumlah kuantitas kebutuhan.',
            'quantity.min' => 'Jumlah kebutuhan minimal 0.1 unit.',
            'hours_until_deadline.required' => 'Tentukan batas waktu maksimal menunggu.',
            'hours_until_deadline.min' => 'Batas waktu minimal 1 jam.',
            'urgency.required' => 'Pilih tingkat urgensi kebutuhan Anda.',
            'delivery_district.required' => 'Kecamatan pengiriman wajib diisi.',
            'delivery_address.required' => 'Alamat pengiriman wajib diisi.',
        ];
    }
}
