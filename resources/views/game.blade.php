<x-app-layout>

    <x-slot name="header"></x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">



                    <div class="dealer">
                        <div class="dealer-cards">
                            Dealer Cards here
                        </div>
                    </div>
                    <div class="player">
                        <div class="player-cards">
                            Player Cards here
                        </div>
                        <div class="player-chips">
                            Player Chips here
                        </div>
                        <div class="stake-chips">
                            Stake Chips here
                        </div>
                        <div class="player-actions">
                            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Stand
                            </button>
                            <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Hit
                            </button>
                            <button class="dd-button bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded hidden">
                                Double Down
                            </button>
                            <button class="split-button bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded hidden">
                                Split
                            </button>
                            <form action="/post-bet" class="post-bet" method="post">
                                @csrf
                                <input type="number" min="0" name="bet">
                                <button type="submit">Place Bet</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
