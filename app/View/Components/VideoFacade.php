<?php

namespace App\View\Components;

use Illuminate\View\Component;

class VideoFacade extends Component
{
    public $videoId;
    public $videoSource;
    public $vimeoId;
    public $externalVideoId;
    public $muxPlaybackId;
    public $thumbnail;
    public $title;
    public $duration;
    public $levelDisplay;
    public $subject;
    public $instructor;
    public $year;
    public $lessonId;
    public $courseId;
    public $showLevelBadge;
    public $showDuration;
    public $showPlayOverlay;
    public $lazyLoad;
    public $class;

    /**
     * Create a new component instance.
     */
    public function __construct(
        $videoId = null,
        $videoSource = 'local',
        $vimeoId = null,
        $externalVideoId = null,
        $muxPlaybackId = null,
        $thumbnail = null,
        $title = '',
        $duration = '',
        $levelDisplay = '',
        $subject = '',
        $instructor = '',
        $year = '',
        $lessonId = null,
        $courseId = null,
        $showLevelBadge = true,
        $showDuration = true,
        $showPlayOverlay = true,
        $lazyLoad = true,
        $class = 'lesson-card'
    ) {
        $this->videoId = $videoId;
        $this->videoSource = $videoSource;
        $this->vimeoId = $vimeoId;
        $this->externalVideoId = $externalVideoId;
        $this->muxPlaybackId = $muxPlaybackId;
        $this->thumbnail = $thumbnail;
        $this->title = $title;
        $this->duration = $duration;
        $this->levelDisplay = $levelDisplay;
        $this->subject = $subject;
        $this->instructor = $instructor;
        $this->year = $year;
        $this->lessonId = $lessonId;
        $this->courseId = $courseId;
        $this->showLevelBadge = $showLevelBadge;
        $this->showDuration = $showDuration;
        $this->showPlayOverlay = $showPlayOverlay;
        $this->lazyLoad = $lazyLoad;
        $this->class = $class;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.video-facade');
    }
}