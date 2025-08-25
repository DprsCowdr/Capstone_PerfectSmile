<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceItemModel extends Model
{
    protected $table = 'invoice_items';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'invoice_id', 'description', 'quantity', 'unit_price', 'total', 'tax', 'discount', 'created_at', 'updated_at'
        // Add or adjust fields as needed to match your invoice_items table
    ];
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Get all items for a specific invoice
    public function getItemsByInvoice($invoiceId)
    {
        return $this->where('invoice_id', $invoiceId)->findAll();
    }

    // Calculate totals for an invoice
    public function getInvoiceTotals($invoiceId)
    {
        $items = $this->getItemsByInvoice($invoiceId);
        $subtotal = 0;
        $total_tax = 0;
        $total_discount = 0;
        $total_amount = 0;
        foreach ($items as $item) {
            $subtotal += $item['unit_price'] * $item['quantity'];
            $total_tax += $item['tax'] ?? 0;
            $total_discount += $item['discount'] ?? 0;
            $total_amount += $item['total'];
        }
        return [
            'subtotal' => $subtotal,
            'total_tax' => $total_tax,
            'total_discount' => $total_discount,
            'total_amount' => $total_amount
        ];
    }

    // Delete a single item
    public function deleteItem($itemId)
    {
        return $this->delete($itemId);
    }
}
