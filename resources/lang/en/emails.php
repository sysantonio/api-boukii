<?php

return [
    'bookingCreate' =>
        ['subject' => 'Your reservation at Boukii',
            'greeting' => 'Hello :userName,',
            'reservation_request' => 'We have received a reservation request with reference :reference, for',
            'singular_course' => 'the following course:',
            'plural_courses' => 'the following courses:',
            'course_count' => ':countx :name',
            'singular_date' => 'Date:',
            'plural_dates' => 'Dates:',
            'singular_participant' => 'Participant:',
            'plural_participants' => 'Participants:',
            'instructor' => 'Instructor: :monitor',
            'refund_guarantee' => '+ Refund Guarantee',
            'booking_notes' => ':bookingNotes',
            'sincerely' => 'Sincerely,',
            'school_name' => 'The :schoolName School'],
    'bookingPay' =>
        ['subject' => 'Pay your reservation at Boukii'],
    'bookingCancel' =>
        ['subject' => 'Cancelled your reservation at Boukii',
            'cancellation_subject' => 'Boukii Booking Cancellation',
            'cancellation_greeting' => 'Hello :userName,',
            'cancellation_intro' => 'The booking with reference <strong>:reference</strong> has been canceled for',
            'single_course' => 'the following course:',
            'multiple_courses' => 'the following courses:',
            'course_count' => ':countx :name',
            'single_date' => 'Date:',
            'multiple_dates' => 'Dates:',
            'single_user' => 'Participant:',
            'multiple_users' => 'Participants:',
            'booking_notes' => ':bookingNotes',
            'cancellation_regards' => 'Best regards, :schoolName School'
        ],
    'recoverPassword' =>
        ['subject' => 'Change your password at Boukii',
            'reset_password_subject' => 'Reset Boukii Password',
            'reset_password_greeting' => 'Hello :userName,',
            'reset_password_intro' => 'To reset your password on Boukii, follow the link below:',
            'reset_password_button' => 'Reset My Password',
            'reset_password_outro' => 'If you don\'t want to reset your password, you can ignore this email. Your password will not be changed.',
            'reset_password_regards' => 'Best regards, The Boukii Team',],
    'welcomeTo' =>
        ['subject' => 'Register at Boukii completed',
            'welcome' => 'Hello :userName, Welcome to Boukii, you can now log in with your account.',
            'regards' => 'Best regards, The Boukii Team'],
    'bookingNoticePay' =>
        ['subject' => 'Pay your reservation at Boukii'],
    'bookingInfo' =>
        ['subject' => 'Your reservation at Boukii'],
    'footer' => [
        'button_copy_url' => 'Button Copy URL',
        'automatic_email' => 'This email has been generated automatically and cannot receive replies.',
        'contact_school' => 'For more information, contact :schoolName school.',
        'contact_boukii' => 'For more information, visit the Boukii Contact Center.',
        'copyright' => 'Boukii © 2024',
        'school_conditions' => 'School General Sales Conditions',
        'boukii_conditions' => 'Terms of Use',
    ]

];
