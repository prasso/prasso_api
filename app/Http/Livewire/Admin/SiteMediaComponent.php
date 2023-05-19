<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Models\SiteMedia;


class SiteMediaComponent extends Component
{
    protected $listeners = ['media_edit','media_delete'];

    public $media_id; //
    public $s3media_url; //
    public $thumb_url; //
    public $video_duration; //
    public $dimensions; //
    public $media_title; //
    public $media_description; //
    public $media_date; //


        
    public function mount(SiteMedia $media)
    {
        if ($media == null) return;
        $this->media_id = $media->id;
        $this->s3media_url = $media->s3media_url;
        $this->thumb_url = $media->thumb_url;
        $this->video_duration = $media->video_duration;
        $this->dimensions = $media->dimensions;
        $this->media_title = $media->media_title;
        $this->media_description = $media->media_description;
        $this->media_date = $media->media_date;
    }

    public function render()
    {
        return view('livewire.admin.site-media-component' );
    }

    public function store()
    {
        $validatedData = $this->validate([
            's3media_url' => 'required|string|max:5000',
            'thumb_url' => 'nullable|string|max:500',
            'video_duration' => 'nullable|string|max:10',
            'dimensions' => 'nullable|string',
            'media_title' => 'nullable|string|max:500',
            'media_description' => 'nullable|string|max:1500',
            'media_date' => 'nullable|string|max:500',
        ]);

        if ($this->media_id) {
            SiteMedia::find($this->media_id)->update($validatedData);
        } else {
            SiteMedia::create($validatedData);
        }
        $this->resetFields();
    }
    public function media_edit($id)
    {
        $this->edit($id);
    }

    public function edit($id)
    {
        $media = SiteMedia::findOrFail($id);
        $this->media_id = $media->id;
        $this->s3media_url = $media->s3media_url;
        $this->thumb_url = $media->thumb_url;
        $this->video_duration = $media->video_duration;
        $this->dimensions = $media->dimensions;
        $this->media_title = $media->media_title;
        $this->media_description = $media->media_description;
        $this->media_date = $media->media_date;
    }

    public function media_delete($id)
    {
        $this->delete($id);
    }

    public function delete($id)
    {
        SiteMedia::findOrFail($id)->delete();
    }

    private function resetFields()
    {
        $this->media_id = null;
        $this->s3media_url = null;
        $this->thumb_url = null;
        $this->video_duration = null;
        $this->dimensions = null;
        $this->media_title = null;
        $this->media_description = null;
        $this->media_date = null;
    }
}
