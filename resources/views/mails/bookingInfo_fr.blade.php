@extends('mails.layout')

@section('body')

    @section('body')

        <p>
            Bonjour {{ $userName }},
            <br>
            Voici les détails de votre réservation <strong>{{ $reference }}</strong>:
        </p>

        @foreach ($groupedCourses as $courseId => $courseData)
            <h3>{{ $courseData['course_name'] }}</h3>
            <ul>
                @foreach ($courseData['clients'] as $client)
                    <li>{{ $client }}</li>
                @endforeach
            </ul>
        @endforeach

    <br>

    @if ($booking['has_cancellation_insurance'])
        <h3>+ Remboursement garanti</h3>
    @endif

    <br>

    <p>
        {{ $booking['notes'] }}
    </p>

    <br>
    <h1>Conditions d'annulation</h1>
    <p>
        En cas de modification ou de désistement, merci de nous avertir le plus tôt possible au +41 (0)26 927 55 25.
    </p>
    <p>
        En cas d’absence de l’élève au départ du cours, le prix de celui-ci ne sera pas remboursé et le cours ne sera pas échangé contre un autre.
    </p>
    <p>
        En cas de maladie ou accident, le client s’engage à avertir de l’absence dès que possible, au plus tard 1 heure avant le début du cours.
    </p>

    <p>
        Merci de votre inscription et au plaisir de vous accueillir chez nous !
    </p>
    <p>
        L'équipe de l'Ecole Suisse de Ski Charmey
    </p>
    <p>ess@charmey.ch</p>
    <p>
        +41 (0)26 927 55 25
    </p>

@endsection
