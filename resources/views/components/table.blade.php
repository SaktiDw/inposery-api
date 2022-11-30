@props(['data', 'columns'])

<div class="w-full overflow-x-scroll rounded-lg shadow-xl">
    <table class="bg-white dark:bg-slate-800 rounded-lg table table-auto w-full overflow-hidden">
        <thead>
            <tr class="text-left bg-gradient-to-tl from-green-700 to-lime-500 ">
                @foreach ($columns as $item )
                <th className="py-2 px-4">
                    {{$item->title}}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index=>$item)
            <tr class="odd:bg-slate-50 hover:bg-slate-100 dark:odd:bg-slate-700/50 dark:hover:bg-slate-700">
                @foreach ($columns as $col )
                @if (isset($col->render))
                <td class="py-2 px-4">
                    {{$item[$col->key][$col->render]}}
                </td>
                @elseif (isset($col->dataType) and $col->dataType == "numbering")
                <td class="py-2 px-4">
                    {{ ($data ->currentpage()-1) * $data ->perpage() + $index + 1  }}
                </td>
                @else()
                <td class="py-2 px-4">
                    {{$item[$col->key]}}
                </td>
                @endif

                @endforeach
            </tr>

            @endforeach
        </tbody>
    </table>
    {{$data->links()}}
</div>
