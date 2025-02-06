<table>
    <thead>
    <tr>
        <th>Course ID</th>
        <th>Course Name</th>
        <th>Dates</th>
        <th>Extras Total</th>
        <th>Total Cost</th>
    </tr>
    </thead>
    <tbody>
    @foreach($courses as $course)
        <tr>
            <td>{{ $course['course_id'] }}</td>
            <td>{{ $course['course_name'] }}</td>
            <td>
                @foreach($course['dates'] as $date)
                    {{ $date['date'] }} ({{ $date['hour_start'] }} - {{ $date['hour_end'] }})<br>
                @endforeach
            </td>
            <td>{{ $course['extras_total'] }}</td>
            <td>{{ $course['total_cost'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
