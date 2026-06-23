<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\Locator\LocatorAwareTrait;

class NetDebtSettlementService
{
    use LocatorAwareTrait;

    /**
     * Calculates the minimum peer-to-peer settlement transactions.
     *
     * @param int $sessionId
     * @return array Array of suggested transactions: [['debtor_id' => ID, 'creditor_id' => ID, 'amount' => float]]
     */
    public function calculateSettlements(int $sessionId): array
    {
        $expensesTable = $this->fetchTable('Expenses');

        // Fetch expenses with allocations
        $expenses = $expensesTable->find()
            ->where(['Expenses.session_id' => $sessionId])
            ->contain(['ExpenseAllocations'])
            ->all();

        $balances = []; // participant_id => Net Balance (+ is owed money, - owes money)

        foreach ($expenses as $expense) {
            foreach ($expense->expense_allocations as $allocation) {
                $pid = $allocation->participant_id;
                
                if (!isset($balances[$pid])) {
                    $balances[$pid] = 0.0;
                }

                // If they are the payer, they get credit (+) for the total expense amount.
                // Wait, if an expense can have multiple payers in the future, we need to distribute it.
                // For now, assuming one payer per expense allocation row, or 'is_payer' means they paid the 'amount_owed' part.
                // Let's adopt a standard: The payer gets +total_amount, but wait... 
                // In EquiSplit, a payer usually pays the whole bill, and others owe.
                // Let's refine: is_payer = true means they paid the entire expense.
                if ($allocation->is_payer) {
                    $balances[$pid] += (float)$expense->total_amount;
                }
                
                // They owe their allocated amount (-)
                $balances[$pid] -= (float)$allocation->amount_owed;
            }
        }

        // Apply session charges logically if needed. 
        // Note: For Net-Debt, usually it's used for Long Trips (which might not have session charges).
        // If they do, the CalculationEngineService calculates exact grand_totals. 
        // We'll calculate balances directly from the CalculationEngineService for a robust approach.
        
        $calcEngine = new CalculationEngineService();
        $totals = $calcEngine->calculateParticipantTotals($sessionId);
        
        // Recalculate balances using the precise grand_totals from CalculationEngineService
        // Payer gets (+) the expense total. They owe (-) their grand_total.
        $refinedBalances = [];
        $totalSessionCharges = 0.0;
        
        foreach ($totals as $pid => $data) {
            $refinedBalances[$pid] = -1 * $data['grand_total'];
            $totalSessionCharges += $data['total_charges'];
        }
        
        $totalGroupSubtotal = 0.0;
        $payerBaseCredits = [];
        
        // Sum up base credits
        foreach ($expenses as $expense) {
            $totalGroupSubtotal += (float)$expense->total_amount;
            
            foreach ($expense->expense_allocations as $allocation) {
                if ($allocation->is_payer) {
                    $pid = $allocation->participant_id;
                    if (!isset($payerBaseCredits[$pid])) $payerBaseCredits[$pid] = 0.0;
                    $payerBaseCredits[$pid] += (float)$expense->total_amount;
                }
            }
        }
        
        // Distribute the credit for Session Charges proportionally to whoever paid the base expenses
        foreach ($payerBaseCredits as $pid => $baseCredit) {
            if (!isset($refinedBalances[$pid])) $refinedBalances[$pid] = 0.0;
            
            $chargeCredit = 0.0;
            if ($totalGroupSubtotal > 0) {
                $chargeCredit = ($baseCredit / $totalGroupSubtotal) * $totalSessionCharges;
            }
            
            $refinedBalances[$pid] += ($baseCredit + $chargeCredit);
        }

        // Separate into debtors and creditors
        $debtors = [];
        $creditors = [];

        foreach ($refinedBalances as $pid => $balance) {
            $balance = round($balance, 2);
            if ($balance < 0) {
                $debtors[$pid] = abs($balance);
            } elseif ($balance > 0) {
                $creditors[$pid] = $balance;
            }
        }

        // Greedy minimization
        arsort($debtors);
        arsort($creditors);

        $transactions = [];

        foreach ($debtors as $debtorId => &$debtAmount) {
            foreach ($creditors as $creditorId => &$creditAmount) {
                if ($debtAmount <= 0.05) break;
                if ($creditAmount <= 0.05) continue;

                $settleAmount = min($debtAmount, $creditAmount);
                $debtAmount -= $settleAmount;
                $creditAmount -= $settleAmount;

                $transactions[] = [
                    'debtor_id' => $debtorId,
                    'creditor_id' => $creditorId,
                    'amount' => round($settleAmount, 2)
                ];
            }
        }

        return $transactions;
    }
}
