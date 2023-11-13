@extends('mails.layout')

@section('body')

<p>
    Bonjour {{ $userName }},
    <br>
    Nous vous enverrons le courriel suivant pour vous rappeler votre réservation <strong>{{ $reference }}</strong>, pour
    @if (count($courses) == 1)
        le cours suivant:
    @else
        les cours suivants:
    @endif
</p>

@foreach ($courses as $key => $cType)
   @if($key == 'collective' && count($cType))
        <h1>Cours Collective</h1>
    @endif
    @if($key == 'private' && count($cType))
        <h1>Cours Privés</h1>
    @endif
    @foreach ($cType as $c)
        <h3>
            {{ count($c['users']) . 'x ' . $c['name'] }}
        </h3>
        <ul>
            <li>
                @if (count($c['dates']) <= 1)
                    Date:
                @else
                    Dates:
                @endif
                {{ implode(', ', $c['dates']) }}.
            </li>
            <li>
                @if (count($c['users']) <= 1)
                    Participant:
                @else
                    Participants:
                @endif
                {{ implode(', ', $c['users']) }}.
            </li>
        </ul>
    @endforeach
    <br>
   @if($key == 'collective' && count($cType))
        <div>
            <h4>Horaire des cours :</h4>
            <div>
                <u>Cours du matin</u>
                <li>
                    9h10 : rendez-vous au départ de la télécabine, où les enfants seront pris en charge par le moniteur
                </li>
                <li>
                    9h45 : début du cours
                </li>
                <li>
                    11h45 : fin du cours au sommet de la télécabine
                </li>
                <br>
                <u>Cours de l'après-midi</u>
                <li>
                    13h45: rendez-vous au sommet de la télécabine, où les enfants seront pris en charge par le moniteur
                </li>
                <li>
                    14h00 : début du cours
                </li>
                <li>
                    16h00 : fin du cours au sommet de la télécabine
                </li>
            </div>
            <br>
            <h4>Autres informations</h4>
            <div>
                <li>Nos cours collectifs sont donnés uniquement en français.</li>
                <li> Nous ne louons pas de matériel ni à l'accueil ni au sommet des remontées mécaniques. Nous vous conseillons de vous adresser à un magasin de sport près de chez vous ou à <a target="_blank" href="https://www.charmeysports.ch/"> Charmey Sports</a>.. Attention, nous vous déconseillons de vouloir louer du matériel le premier jour de cours collectifs, car vous risquez de manquer le rendez-vous de 9h10.
                </li>  </div>
            <br>
            <h4>Conseils aux parents</h4>
            <div>
                <li> Quelques jours avant le cours, expliquez à votre enfant qu’il va suivre un cours avec d’autres enfants et un moniteur afin qu’il puisse s’y préparer.</li>
                <li> Pensez à habiller votre enfant en fonction de la météo. Nous vous rappelons qu’une combinaison de ski, des gants, un casque et des lunettes sont obligatoires. N’oubliez pas la crème solaire !</li>
                <li> Nous comptons sur vous pour être à l’heure, autant au début qu’à la fin du cours.</li>
                <li> Nous vous demandons de ne pas suivre le cours afin que votre enfant ne soit pas distrait et puisse se concentrer sur les exercices.</li>
                <li> Le but est que l’enfant ait du plaisir à suivre le cours et qu’il progresse à son rythme. L’apprentissage doit rester un plaisir !</li>
            </div>
            @endif
            @if($key == 'private' && count($cType))
                <div>
                    <h4>Horaire des cours :</h4>
                    <div>
                        <p> Nous vous donnons rendez-vous 5 minutes avant le début du cours au sommet de la télécabine. Pensez qu'il faut compter environ 15 minutes pour monter.
                        </p>
                        <p> En cas de retard, la durée du cours initialement prévue ne pourra pas être garantie. ELes jours de forte affluence, n'hésitez pas à passer devant la file en vous annonçant comme élève de l'école de ski, ceci afin d'arriver à l'heure au cours.
                        </p>
                        <p>Pour gagner du temps, n'hésitez pas à acheter votre forfait en ligne : https://shop.e-guma.ch/telecharmey/fr/tickets</p>
                    </div>
                    <br>
                    <h4>Informations spécifiques pour les enfants</h4>

                    <li> Moins de 6 ans : l'aller-retour est gratuit et il est obligatoire d'accompagner l'enfant dans la cabine.</li>
                    <li> 6 à 8 ans : l'aller-retour est payant et l’enfant peut prendre seul la télécabine uniquement avec l’autorisation écrite d’un parent (à donner à l'accueil).</li>
                    <li> Dès 8 ans : l'aller-retour est payant et l'enfant peut prendre seul la cabine.</li>
                </div>
            @endif
            <br>
            @endforeach

            @if ($hasCancellationInsurance)
                <h3>+ Remboursement garanti</h3>
            @endif

            <br>

            <p>
                {{ $bookingNotes }}
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
                En cas de maladie ou accident, le client s’engage à avertir de l’absence dès que possible, au plus tard 1 heure avant le début du cours. Celui-ci pourra être reporté ou remboursé uniquement si l'option remboursement a été choisie lors de la réservation. Celle-ci peut être rajoutée dans un délai de 24 heures après la réservation uniquement par email à l'adresse ess@charmey.ch.

            </p>

            Si un cours doit être annulé, par exemple en cas fermeture de la télécabine, nous vous avertirons par SMS. Nous essayons au maximum de déplacer les cours à la station de Jaun, à 10 minutes de route de la sortie du village de Charmey.


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
