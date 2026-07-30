<!DOCTYPE html>
<html>

<head>

    <title>
        Performance Dashboard
    </title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>


<body>


    <div class="container mt-5">


        <h2>
            📊 Performance Monitoring Dashboard
        </h2>



        <div class="row mt-4">


            <div class="col-md-3">

                <div class="card p-3">

                    <h5>Total Requests</h5>

                    <h2>
                        {{ $totalRequests }}
                    </h2>

                </div>

            </div>



            <div class="col-md-3">

                <div class="card p-3">

                    <h5>
                        Average Response
                    </h5>

                    <h2>
                        {{ round($averageResponse,2) }} ms
                    </h2>

                </div>

            </div>



            <div class="col-md-3">

                <div class="card p-3">

                    <h5>
                        Slow Requests
                    </h5>

                    <h2>
                        {{ $slowRequests }}
                    </h2>

                </div>

            </div>



            <div class="col-md-3">

                <div class="card p-3">

                    <h5>
                        Memory Usage
                    </h5>

                    <h2>
                        {{ round($totalMemory/1024,2) }} KB
                    </h2>

                </div>

            </div>


        </div>





        <h3 class="mt-5">
            Recent Requests
        </h3>



        <table class="table table-bordered">


            <tr>

                <th>
                    Method
                </th>

                <th>
                    URL
                </th>

                <th>
                    Status
                </th>

                <th>
                    Time
                </th>

                <th>
                    Slow
                </th>

            </tr>



            @foreach($recentLogs as $log)


            <tr>

                <td>
                    {{ $log->method }}
                </td>


                <td>
                    {{ $log->url }}
                </td>


                <td>
                    {{ $log->status_code }}
                </td>


                <td>
                    {{ $log->response_time }} ms
                </td>


                <td>

                    @if($log->is_slow)

                    <span class="badge bg-danger">
                        YES
                    </span>

                    @else

                    <span class="badge bg-success">
                        NO
                    </span>

                    @endif


                </td>


            </tr>


            @endforeach


        </table>



    </div>


</body>

</html>