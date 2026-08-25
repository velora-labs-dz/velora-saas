<?php

namespace App\Http\Controllers;

use App\Actions\Payments\RecordPaymentAction;
use App\Actions\Payments\RefundPaymentAction;
use App\Actions\Payments\VoidPaymentAction;
use App\Http\Requests\Payments\RecordPaymentRequest;
use App\Http\Requests\Payments\RefundPaymentRequest;
use App\Http\Requests\Payments\VoidPaymentRequest;
use App\Models\Client;
use App\Models\Membership;
use App\Models\Payment;
use App\Support\CurrentOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Same IDOR-safe pattern as every controller since Clients: every lookup
 * resolves through CurrentOrganization::organizationOrFail()->payments()
 * rather than a global Payment::findOrFail(). See docs/SECURITY.md §5.
 *
 * No update route — a payment is append-oriented per
 * docs/VELORA_SOURCE_OF_TRUTH.md §2.4. Void and refund are the only two
 * ways an existing payment record changes.
 */
class PaymentController extends Controller
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    public function index(Request $request): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('viewAny', [Payment::class, $organization]);

        $date = $request->string('date')->toString() ?: now()->toDateString();

        $payments = $organization->payments()
            ->with(['client', 'membership.membershipPlan'])
            ->whereDate('paid_at', $date)
            ->orderByDesc('paid_at')
            ->get()
            ->map(fn (Payment $payment) => $this->present($payment));

        $membership = $this->currentOrganization->membership();

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'date' => $date,
            'canManage' => $membership?->role->canManagePayments() ?? false,
            'canCorrect' => $membership?->role->canCorrectPayments() ?? false,
            'clients' => $organization->clients()
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->map(fn (Client $client) => [
                    'id' => $client->id,
                    'full_name' => $client->fullName(),
                ]),
            'memberships' => $organization->memberships()
                ->with('membershipPlan')
                ->whereIn('status', ['draft', 'active', 'frozen'])
                ->get()
                ->map(fn (Membership $membership) => [
                    'id' => $membership->id,
                    'client_id' => $membership->client_id,
                    'label' => $membership->membershipPlan->name.' — '.$membership->remaining_amount.' '.$membership->currency.' remaining',
                ]),
        ]);
    }

    public function store(RecordPaymentRequest $request, RecordPaymentAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('create', [Payment::class, $organization]);

        $payment = $action->handle($organization, $request->validated(), $request->user());

        return redirect()
            ->route('payments.index', ['date' => $payment->paid_at->toDateString()])
            ->with('success', 'Payment recorded.');
    }

    public function void(VoidPaymentRequest $request, int $payment, VoidPaymentAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->payments()->findOrFail($payment);

        Gate::authorize('void', [$model, $organization]);

        $action->handle($model, $request->validated()['void_reason']);

        return redirect()
            ->route('payments.index', ['date' => $model->paid_at->toDateString()])
            ->with('success', 'Payment voided.');
    }

    public function refund(RefundPaymentRequest $request, int $payment, RefundPaymentAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->payments()->findOrFail($payment);

        Gate::authorize('refund', [$model, $organization]);

        $validated = $request->validated();

        $action->handle($model, (string) $validated['amount'], $validated['refund_reason']);

        return redirect()
            ->route('payments.index', ['date' => $model->paid_at->toDateString()])
            ->with('success', 'Payment refunded.');
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'status' => $payment->status->value,
            'status_label' => $payment->status->label(),
            'client' => [
                'id' => $payment->client->id,
                'full_name' => $payment->client->fullName(),
            ],
            'membership' => $payment->membership ? [
                'id' => $payment->membership->id,
                'plan_name' => $payment->membership->membershipPlan->name,
            ] : null,
            'amount' => (string) $payment->amount,
            'currency' => $payment->currency,
            'method' => $payment->method->value,
            'method_label' => $payment->method->label(),
            'reference' => $payment->reference,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'refunded_amount' => (string) $payment->refunded_amount,
            'refund_reason' => $payment->refund_reason,
            'voided_at' => $payment->voided_at?->toIso8601String(),
            'void_reason' => $payment->void_reason,
            'notes' => $payment->notes,
            'can_void' => $payment->status->canVoid(),
            'can_refund' => $payment->status->canRefund(),
            'refundable_amount' => bcsub((string) $payment->amount, (string) $payment->refunded_amount, 2),
        ];
    }
}
