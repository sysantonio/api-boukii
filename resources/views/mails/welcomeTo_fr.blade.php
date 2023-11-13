@extends('mails.layout')

@section('body')
<p>
    Bonjour {{ $userName }},
    <br>
    Bienvenue sur Boukii, vous pouvez maintenant vous connecter avec votre compte.
</p>

<br>

<p>
    Cordialement,
    <br>
    L'équipe Boukii
</p>
@endsection