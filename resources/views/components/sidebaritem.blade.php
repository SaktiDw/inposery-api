@props(['href', 'active','icon'])
@php

$classes = $active
? "bg-white shadow-lg dark:bg-slate-800 dark:text-slate-100"
: 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700
hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';

$iClases = $active
? 'bg-gradient-to-tl from-green-700 to-lime-500 text-white'
: 'bg-white shadow-xl text-slate-800 dark:bg-slate-800 dark:text-slate-100';

@endphp
<a href="{{$href}}" {{ $attributes->merge(['class' => 'p-2 rounded-lg flex outline-none group hover:bg-white hover:text-slate-800 hover:shadow-lg hover:scale-105 transition-all ease-in-out duration-200
        dark:hover:bg-slate-800 dark:hover:text-slate-100
        $classes']) }}>
    <i class="w-10 h-10 rounded-lg flex justify-center items-center transition-all ease-in-out duration-200 shadow group-hover:text-white group-hover:bg-gradient-to-tl from-green-700 to-lime-500
    {{$iClases}}
    {{$icon}}
    "></i>
    <span class="p-2">
        {{$slot}}</span>

</a>