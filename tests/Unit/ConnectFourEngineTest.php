<?php

namespace Tests\Unit;

use App\Support\ConnectFourEngine;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConnectFourEngineTest extends TestCase
{
    /**
     * @return array<int, array<int, int>>
     */
    protected function emptyBoard(): array
    {
        return array_fill(0, 6, array_fill(0, 7, ConnectFourEngine::EMPTY));
    }

    #[Test]
    public function test_drop_token_returns_the_lowest_free_row(): void
    {
        $board = $this->emptyBoard();

        $firstRow = ConnectFourEngine::dropToken($board, 2, ConnectFourEngine::PLAYER);
        $secondRow = ConnectFourEngine::dropToken($board, 2, ConnectFourEngine::ARJAN);

        $this->assertSame(5, $firstRow);
        $this->assertSame(4, $secondRow);
        $this->assertSame(ConnectFourEngine::PLAYER, $board[5][2]);
    }

    #[Test]
    public function test_drop_token_rejects_a_full_column(): void
    {
        $board = $this->emptyBoard();
        for ($row = 0; $row < 6; $row++) {
            $board[$row][0] = ConnectFourEngine::PLAYER;
        }

        $this->assertNull(ConnectFourEngine::dropToken($board, 0, ConnectFourEngine::ARJAN));
    }

    #[Test]
    public function test_winning_cells_detect_a_diagonal_of_four(): void
    {
        $board = $this->emptyBoard();
        $board[5][0] = ConnectFourEngine::PLAYER;
        $board[4][1] = ConnectFourEngine::PLAYER;
        $board[3][2] = ConnectFourEngine::PLAYER;
        $board[2][3] = ConnectFourEngine::PLAYER;

        $cells = ConnectFourEngine::winningCells($board, 2, 3, ConnectFourEngine::PLAYER);

        $this->assertCount(4, $cells);
    }

    #[Test]
    public function test_arjan_takes_an_immediate_win(): void
    {
        $board = $this->emptyBoard();
        $board[5][0] = ConnectFourEngine::ARJAN;
        $board[5][1] = ConnectFourEngine::ARJAN;
        $board[5][2] = ConnectFourEngine::ARJAN;
        $board[5][4] = ConnectFourEngine::PLAYER;

        $this->assertSame(3, ConnectFourEngine::chooseArjanMove($board));
    }

    #[Test]
    public function test_arjan_blocks_an_immediate_player_win(): void
    {
        $board = $this->emptyBoard();
        $board[5][0] = ConnectFourEngine::PLAYER;
        $board[5][1] = ConnectFourEngine::PLAYER;
        $board[5][2] = ConnectFourEngine::PLAYER;
        $board[0][6] = ConnectFourEngine::ARJAN;

        $this->assertSame(3, ConnectFourEngine::chooseArjanMove($board));
    }

    #[Test]
    public function test_arjan_choice_is_deterministic_for_the_same_board(): void
    {
        $board = $this->emptyBoard();
        $board[5][3] = ConnectFourEngine::PLAYER;

        $this->assertSame(
            ConnectFourEngine::chooseArjanMove($board),
            ConnectFourEngine::chooseArjanMove($board)
        );
    }
}
