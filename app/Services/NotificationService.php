<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProposalStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductProposal;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Derives the role-aware notification feed shown in the bell dropdown and its
 * numeric badge. Everything is computed live from domain state, so the badge
 * can never drift out of sync with the underlying orders and shipments.
 */
class NotificationService
{
    /**
     * @return Collection<int, array{label: string, url: string, tone: string, count: int}>
     */
    public function forUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return match (true) {
            $user->hasRole(UserRole::ADMIN->value) => $this->forAdmin(),
            $user->hasRole(UserRole::DISTRIBUTOR->value) => $this->forDistributor($user),
            $user->hasRole(UserRole::KURIR->value) => $this->forCourier($user),
            default => $this->forUmkm($user),
        };
    }

    /**
     * @return Collection<int, array{label: string, url: string, tone: string, count: int}>
     */
    private function forUmkm(User $user): Collection
    {
        $umkm = $user->umkm;
        if (! $umkm) {
            return collect();
        }

        $items = collect();

        $unpaid = OrderItem::where('umkm_id', $umkm->id)
            ->where('payment_status', PaymentStatus::UNPAID->value)
            ->count();
        $items->push($this->item($unpaid, 'tagihan menunggu pembayaran', route('umkm.orders.index'), 'brass'));

        $awaitingConfirmation = OrderItem::where('umkm_id', $umkm->id)
            ->whereNull('received_confirmed_at')
            ->whereHas('order', fn ($query) => $query->whereIn('status', [
                OrderStatus::DELIVERED->value,
                OrderStatus::COMPLETED->value,
            ]))
            ->count();
        $items->push($this->item($awaitingConfirmation, 'paket menunggu konfirmasi terima', route('umkm.orders.index'), 'forest'));

        $inTransit = Shipment::where('status', 'IN_TRANSIT')
            ->whereHas('order.items', fn ($query) => $query->where('umkm_id', $umkm->id))
            ->count();
        $items->push($this->item($inTransit, 'pengiriman sedang berjalan', route('umkm.tracking.index'), 'forest'));

        $awaitingReview = OrderItem::where('umkm_id', $umkm->id)
            ->whereNotNull('received_confirmed_at')
            ->whereDoesntHave('review')
            ->count();
        $items->push($this->item($awaitingReview, 'pesanan menunggu ulasan', route('umkm.orders.index'), 'brass'));

        return $items->filter(fn (array $item) => $item['count'] > 0)->values();
    }

    /**
     * @return Collection<int, array{label: string, url: string, tone: string, count: int}>
     */
    private function forDistributor(User $user): Collection
    {
        $distributor = $user->distributor;
        if (! $distributor) {
            return collect();
        }

        $items = collect();

        $toConfirm = Order::where('distributor_id', $distributor->id)
            ->where('status', OrderStatus::PAID->value)
            ->count();
        $items->push($this->item($toConfirm, 'pesanan baru menunggu konfirmasi', route('distributor.orders.index'), 'brass'));

        $readyToShip = Order::where('distributor_id', $distributor->id)
            ->where('status', OrderStatus::READY_TO_SHIP->value)
            ->count();
        $items->push($this->item($readyToShip, 'pesanan siap kirim, tugaskan kurir', route('distributor.orders.index'), 'brass'));

        $inTransit = Shipment::where('status', 'IN_TRANSIT')
            ->whereHas('order', fn ($query) => $query->where('distributor_id', $distributor->id))
            ->count();
        $items->push($this->item($inTransit, 'pengiriman sedang berjalan', route('distributor.orders.index'), 'forest'));

        $awaitingUmkm = Order::where('distributor_id', $distributor->id)
            ->where('status', OrderStatus::DELIVERED->value)
            ->count();
        $items->push($this->item($awaitingUmkm, 'pesanan sampai, menunggu konfirmasi UMKM', route('distributor.orders.index'), 'forest'));

        return $items->filter(fn (array $item) => $item['count'] > 0)->values();
    }

    /**
     * @return Collection<int, array{label: string, url: string, tone: string, count: int}>
     */
    private function forCourier(User $user): Collection
    {
        $courier = $user->courier;
        if (! $courier) {
            return collect();
        }

        $items = collect();

        $newTasks = Shipment::where('courier_id', $courier->id)
            ->whereIn('status', ['PENDING', 'ASSIGNED_TO_COURIER'])
            ->count();
        $items->push($this->item($newTasks, 'tugas pengiriman baru', route('kurir.shipments.index'), 'brass'));

        $active = Shipment::where('courier_id', $courier->id)
            ->where('status', 'IN_TRANSIT')
            ->count();
        $items->push($this->item($active, 'pengiriman sedang berjalan', route('kurir.shipments.index'), 'forest'));

        $doneToday = Shipment::where('courier_id', $courier->id)
            ->where('status', 'DELIVERED')
            ->whereDate('delivered_at', now()->toDateString())
            ->count();
        $items->push($this->item($doneToday, 'pengiriman selesai hari ini', route('kurir.shipments.index'), 'forest'));

        return $items->filter(fn (array $item) => $item['count'] > 0)->values();
    }

    /**
     * @return Collection<int, array{label: string, url: string, tone: string, count: int}>
     */
    private function forAdmin(): Collection
    {
        $items = collect();

        $pendingProposals = ProductProposal::where('status', ProposalStatus::PENDING)->count();
        $items->push($this->item($pendingProposals, 'usulan produk menunggu review', route('admin.proposals.index'), 'brass'));

        return $items->filter(fn (array $item) => $item['count'] > 0)->values();
    }

    /**
     * @return array{label: string, url: string, tone: string, count: int}
     */
    private function item(int $count, string $label, string $url, string $tone): array
    {
        return [
            'count' => $count,
            'label' => $label,
            'url' => $url,
            'tone' => $tone,
        ];
    }
}
