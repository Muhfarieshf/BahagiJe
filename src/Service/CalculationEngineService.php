<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\Locator\LocatorAwareTrait;

class CalculationEngineService
{
    use LocatorAwareTrait;

    /**
     * Calculates the final total owed by each participant,
     * including proportional or equal session charges.
     * 
     * @param int $sessionId
     * @return array [participant_id => ['subtotal' => float, 'total_charges' => float, 'grand_total' => float]]
     */
    public function calculateParticipantTotals(int $sessionId): array
    {
        $expensesTable = $this->fetchTable('Expenses');
        $sessionChargesTable = $this->fetchTable('SessionCharges');

        // Get all expenses and their allocations
        $expenses = $expensesTable->find()
            ->where(['Expenses.session_id' => $sessionId])
            ->contain(['ExpenseAllocations'])
            ->all();

        // Get all session charges
        $charges = $sessionChargesTable->find()
            ->where(['SessionCharges.session_id' => $sessionId])
            ->all();

        $sessionsTable = $this->fetchTable('GroupSessions');
        $session = $sessionsTable->get($sessionId, ['contain' => ['Participants']]);

        $participantTotals = [];
        // Pre-initialize all participants to solve the "Free Rider" problem
        foreach ($session->participants as $participant) {
            $participantTotals[$participant->id] = ['subtotal' => 0.0, 'total_charges' => 0.0, 'grand_total' => 0.0];
        }

        $groupSubtotal = 0.0;

        // Calculate Subtotals per participant
        foreach ($expenses as $expense) {
            foreach ($expense->expense_allocations as $allocation) {
                $pid = $allocation->participant_id;
                // Add to their subtotal
                $amountOwed = (float)$allocation->amount_owed;
                $participantTotals[$pid]['subtotal'] += $amountOwed;
                $groupSubtotal += $amountOwed;
            }
        }

        // Apply Session Charges (Taxes, Service Charges, Flat Fees)
        foreach ($charges as $charge) {
            $chargeValue = (float)$charge->charge_value;
            $totalChargeAmount = 0.0;
            
            // Calculate the exact total amount this charge adds to the whole group
            if ($charge->applies_to === 'proportional' || $charge->applies_to === 'equal') {
                if ($charge->charge_type === 'percentage') {
                    $totalChargeAmount = $groupSubtotal * ($chargeValue / 100);
                } elseif ($charge->charge_type === 'flat') {
                    $totalChargeAmount = $chargeValue;
                }
            }
            
            $totalChargeAmountRounded = round($totalChargeAmount, 2);
            $accumulatedCharge = 0.0;
            
            $numParticipants = count($participantTotals);
            if ($numParticipants > 0) {
                // Find the appropriate last participant to absorb remainders
                $pids = array_keys($participantTotals);
                $lastPidForEqual = end($pids);
                
                $lastPidForProportional = null;
                $reversedPids = array_reverse($pids);
                foreach ($reversedPids as $p) {
                    if ($participantTotals[$p]['subtotal'] > 0) {
                        $lastPidForProportional = $p;
                        break;
                    }
                }
                
                $lastPidToUse = ($charge->applies_to === 'proportional' && $lastPidForProportional !== null) 
                                ? $lastPidForProportional 
                                : $lastPidForEqual;
                
                foreach ($participantTotals as $pid => &$totals) {
                    $subtotal = $totals['subtotal'];
                    $myCharge = 0.0;
                    
                    if ($pid === $lastPidToUse) {
                        // Last participant absorbs the exact remainder to prevent rounding drift
                        $myCharge = round($totalChargeAmountRounded - $accumulatedCharge, 2);
                    } else {
                        if ($charge->applies_to === 'proportional' && $groupSubtotal > 0) {
                            $myCharge = round(($subtotal / $groupSubtotal) * $totalChargeAmountRounded, 2);
                        } elseif ($charge->applies_to === 'equal') {
                            $myCharge = round($totalChargeAmountRounded / $numParticipants, 2);
                        }
                        $accumulatedCharge += $myCharge;
                    }
                    
                    $totals['total_charges'] += $myCharge;
                }
            }
        }

        // Finalize Grand Totals
        foreach ($participantTotals as $pid => &$totals) {
            $totals['total_charges'] = round($totals['total_charges'], 2);
            $totals['grand_total'] = round($totals['subtotal'] + $totals['total_charges'], 2);
        }

        return $participantTotals;
    }
}
