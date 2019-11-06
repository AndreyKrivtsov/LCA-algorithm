<?php

namespace App\Http\Controllers;

use App\User;
use App\Event;
use App\City;
use App\Place;
use App\Tag;
use App\Event_tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class EventController extends Controller
{
    protected $data;

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function showMyEvents()
    {
        $data['title'] = 'showMyEvents';
        // ...
        return view('events.events', $data);
    }
    
    public function showMySubs()
    {
        $data['title'] = 'showMySubs';
        // ...
        return view('events.subs', $data);
    }
    
    public function showNewEvent()
    {
        $data['title'] = 'showNewEvent';
        // ...
        return view('events.new', $data);
    }
    
    public function saveNewEvent(Request $request)
    {
        $data = $this->prepareNewEvent($request);
        
        $event = new Event;
        $event->owner_id = $data['user_id'];
        $event->place_id = $data['place_id'];
        $event->date_start = $data['date_start'];
        $event->date_end = $data['date_end'];
        $event->title = $data['title'];
        $event->description = $data['desc'];
        $event->image = '';
        $event->save();
        
        $data['event_id'] = $event->id;
        
        $this->addTagsToEvent($data);
        
        //dd($event);
        return view('events.events', $data);
    }
    
    private function prepareNewEvent($request) {
        $validated_data = $this->requestValidate($request);
        $validated_data['user_id'] = Auth::id();

        $city = $this->getCityOrCreate($validated_data);
        $validated_data['city_id'] = $city->id;

        $place = $this->getPlaceOrCreate($validated_data);
        $validated_data['place_id'] = $place->id;
        
        return $validated_data;
    }
    
    private function getCityOrCreate($data)
    {
        $city = City::firstOrNew(['name' => $data['city']]);
        $city->added_id = $data['user_id'];
        $city->save();

        return $city;
    }
    
    private function saveNewCity($data)
    {
        $city = new City;
        $city->added_id = $data['user_id'];
        $city->name = $data['city'];
        $city->save();
        
        return $city;
    }
    
    private function getPlaceOrCreate($data)
    {
        $place = place::firstOrNew(['title' => $data['place']]);
        $place->added_id = $data['user_id'];
        $place->city_id = $data['city_id'];
        $place->address = $data['address'];
        $place->save();
        
        return $place;
    }
    
    private function saveNewPlace($data)
    {
        $place = new Place;
        $place->added_id = $data['user_id'];
        $place->city_id = $data['city_id'];
        $place->title = $data['place'];
        $place->address = $data['address'];
        $place->save();
        
        return $place;
    }
    
    private function addTagsToEvent($data)
    {
        $tags = $this->getTagsOrCreate($data);
        
        //dd($tags);
    }
    
    private function getTagsOrCreate($data)
    {
        $tags_array = explode(",", $data['tags']);
        $tags_array = array_map('trim', $tags_array);
        $tags = Tag::whereIn('name', $tags_array)->get();
        
        //foreach $tags as $tag
        //if (!$tag) { insert_array = $tag }
        //$insert_tags = insert insert_array
        //$obj_merged = (object) array_merge((array) $tags, (array) $insert_tags);
        
        return $tags;
    }
    
    private function requestValidate($request)
    {
        $data = $request->validate([
            'title' => 'required|max:191',
            'desc' => 'required|max:10000',
            'place' => 'required|max:191',
            'city' => 'required|max:191',
            'address' => 'required|max:191',
            'date_start' => 'required|max:20',
            'date_end' => 'required|max:20',
            'image' => 'image|size:1000|nullable',
            'tags' => 'required|max:191',
        ]);
        
        return $data;
    }
}
