<div class="fi-section-content">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Date</th>
                <th scope="col" class="px-6 py-3">Type</th>
                <th scope="col" class="px-6 py-3">Amount</th>
                <th scope="col" class="px-6 py-3">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="px-6 py-4">{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                    <td class="px-6 py-4">
                        <span @class([
                            'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset',
                            'success' => $transaction->type === 'Deposit',
                            'danger' => $transaction->type === 'Withdraw',
                        ])>
                            {{ $transaction->type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        ${{ number_format($transaction->amount, 2) }}
                    </td>
                    <td class="px-6 py-4">
                        <span @class([
                            'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset',
                            'success' => $transaction->status === 'Done',
                            'warning' => $transaction->status === 'Pending',
                            'danger' => $transaction->status === 'Canceled',
                        ])>
                            {{ $transaction->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
