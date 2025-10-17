@extends("main2.main2")

@section("content")
    <div class="bradcam_area bradcam_bg_1">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="bradcam_text">
                        <h3>{{ $training->title }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ bradcam_area  -->

    <div class="job_details_area">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1">
                    <div class="job_details_header">
                        <div class="single_jobs white-bg d-flex justify-content-between">
                            <div class="jobs_left d-flex align-items-center">
                                <div class="jobs_conetent">
                                    <a href="#"><h4>{{ $training->title }}</h4></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="descript_wrap white-bg">
                        <div class="single_wrap">
                            <h4>Training Video</h4>
                            @if($training->youtube)
                                @php
                                    // Extract YouTube video ID from URL
                                    $youtube_id = '';
                                    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $training->youtube, $match)) {
                                        $youtube_id = $match[1];
                                    }
                                @endphp

                                @if($youtube_id)
                                    <div class="embed-responsive embed-responsive-16by9">
                                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ $youtube_id }}" allowfullscreen></iframe>
                                    </div>
                                @else
                                    <p>Invalid YouTube link provided.</p>
                                @endif
                            @else
                                <p>No video available for this training.</p>
                            @endif
                        </div>

                        @if($training->desc)
                        <div class="single_wrap">
                            <h4>Description</h4>
                            <p>{!! nl2br(e($training->desc)) !!}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
    .embed-responsive {
        position: relative;
        display: block;
        width: 100%;
        padding: 0;
        overflow: hidden;
    }
    .embed-responsive::before {
        content: "";
        display: block;
        padding-top: 56.25%; /* 16:9 Aspect Ratio */
    }
    .embed-responsive .embed-responsive-item {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
</style>
@endsection