<x-app-layout>

    <x-slot name="header"></x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="dealer">
                        <div class="dealer-cards">

                        </div>
                    </div>
                    <div class="player">
                        <div class="player-cards">

                        </div>
                        <div class="player-chips">
                            Balance [<span class="balance">{{ $balance }}</span>]
                        </div>
                        <div class="stake-chips">

                        </div>
                        <div class="player-actions">

                            <button class="stand-button bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded hidden">
                                Stand
                            </button>
                            <button class="hit-button bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded hidden">
                                Hit
                            </button>
                            <button class="double-down-button hidden bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ">
                                Double Down
                            </button>
                            <button class="split-button bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded hidden ">
                                Split
                            </button>
                            <form action="/post-bet" class="post-bet" method="post">
                                @csrf
                                <input type="number" min="0" name="bet">
                                <button type="submit" class="place-bet">Place Bet & Deal</button>
                            </form>
                            <div class="message"></div>
                            <button class="next-hand bg-gray-500 hover:bg-gray900 text-white font-bold py-2 px-4 rounded hidden">Play Next Hand</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
