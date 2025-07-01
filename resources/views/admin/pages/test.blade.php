<x-admin.app-layout>
    <div class="max-h-[500px] overflow-auto rounded-lg">
        <x-table>
            <x-table.head>
                <x-table.row>
                    <x-table.header sortable>Colummn Name 1</x-table.header>
                    <x-table.header>Colummn Name 2</x-table.header>
                    <x-table.header>Colummn Name 3</x-table.header>
                    <x-table.header>Colummn Name 4</x-table.header>
                    <x-table.header>Colummn Name 5</x-table.header>
                </x-table.row>
            </x-table.head>

            <x-table.body>
                @for ($i = 0; $i < 50; $i++)
                    <x-table.row>
                        @for ($j = 0; $j < 5; $j++)
                            <x-table.cell>Cell Name {{ $j + 1 }}</x-table.cell>
                        @endfor
                    </x-table.row>
                @endfor
            </x-table.body>
        </x-table>
    </div>
</x-admin.app-layout>
