<?php
require_once '../libs/fpdf/fpdf.php';
require_once '../includes/db.php';

function generate_eticket($booking_id)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT 
            b.ticket_number, b.passenger_name, b.passenger_email, b.total_price, 
            b.status AS booking_status, b.booking_date, p.payment_status,
            f.flight_number, f.airline, f.origin, f.destination, f.departure_time, 
            f.arrival_time, fs.seat_number
        FROM bookings b
        JOIN flights f ON b.flight_id = f.flight_id
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        LEFT JOIN flight_seats fs ON fs.booking_id = b.booking_id
        WHERE b.booking_id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        throw new Exception("Booking not found");
    }

    $departure = date("D, M j, Y g:i A", strtotime($data['departure_time']));
    $arrival = date("D, M j, Y g:i A", strtotime($data['arrival_time']));
    $booking_date = date("D, M j, Y g:i A", strtotime($data['booking_date']));

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', '', 12);

    // Logo
    $pdf->Image('../assets/images/logo.png', 90, 8, 28);
    $pdf->SetY(40);
    $pdf->SetFillColor(0, 102, 204);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'E-Ticket Confirmation', 0, 1, 'C', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(3);

    // Ticket Details
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(0, 10, 'Ticket Details', 0, 1, 'L', true);
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(90, 8, 'Ticket Number:', 0, 0);
    $pdf->Cell(90, 8, $data['ticket_number'], 0, 1);
    $pdf->Cell(90, 8, 'Booking Date:', 0, 0);
    $pdf->Cell(90, 8, $booking_date, 0, 1);
    $pdf->Cell(90, 8, 'Status:', 0, 0);
    $pdf->Cell(90, 8, ucfirst($data['booking_status']), 0, 1);
    $pdf->Cell(90, 8, 'Payment:', 0, 0);
    $pdf->Cell(90, 8, ucfirst($data['payment_status'] ?? 'N/A'), 0, 1);
    $pdf->Ln(2);

    // Passenger Info
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Passenger Information', 0, 1, 'L', true);
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(90, 8, 'Name:', 0, 0);
    $pdf->Cell(90, 8, $data['passenger_name'], 0, 1);
    $pdf->Cell(90, 8, 'Email:', 0, 0);
    $pdf->Cell(90, 8, $data['passenger_email'], 0, 1);
    if (!empty($data['seat_number'])) {
        $pdf->Cell(90, 8, 'Seat:', 0, 0);
        $pdf->Cell(90, 8, $data['seat_number'], 0, 1);
    }
    $pdf->Ln(2);

    // Flight Info
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Flight Information', 0, 1, 'L', true);
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(90, 8, 'Flight Number:', 0, 0);
    $pdf->Cell(90, 8, $data['flight_number'], 0, 1);
    $pdf->Cell(90, 8, 'Airline:', 0, 0);
    $pdf->Cell(90, 8, $data['airline'], 0, 1);
    $pdf->Cell(90, 8, 'From:', 0, 0);
    $pdf->Cell(90, 8, $data['origin'], 0, 1);
    $pdf->Cell(90, 8, 'To:', 0, 0);
    $pdf->Cell(90, 8, $data['destination'], 0, 1);
    $pdf->Cell(90, 8, 'Departure:', 0, 0);
    $pdf->Cell(90, 8, $departure, 0, 1);
    $pdf->Cell(90, 8, 'Arrival:', 0, 0);
    $pdf->Cell(90, 8, $arrival, 0, 1);
    $pdf->Ln(2);

    // Payment Info
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Payment Summary', 0, 1, 'L', true);
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(90, 8, 'Total Paid:', 0, 0);
    $pdf->Cell(90, 8, number_format($data['total_price'], 2) . ' Birr', 0, 1);
    $pdf->Ln(3);

    // Footer
    $pdf->SetY($pdf->GetPageHeight() - 30);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 6, 'Thank you for booking with us!', 0, 1, 'C');

    $path = "../etickets/eticket-{$booking_id}.pdf";
    $pdf->Output('F', $path);

    return $path;
}
