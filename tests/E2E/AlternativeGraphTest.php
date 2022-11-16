<?php

namespace Finite\Tests\E2E;

use Finite\StateMachine;
use PHPUnit\Framework\TestCase;

class AlternativeArticle
{
    private SimpleArticleState          $state            = SimpleArticleState::DRAFT;
    private AlternativeArticleState     $alternativeState = AlternativeArticleState::NEW;
    private readonly \DateTimeInterface $createdAt;

    public function __construct(public readonly string $title)
    {
        $this->createdAt = new \DateTimeImmutable;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getState(): SimpleArticleState
    {
        return $this->state;
    }

    public function setState(SimpleArticleState $state): void
    {
        $this->state = $state;
    }

    public function getAlternativeState(): AlternativeArticleState
    {
        return $this->alternativeState;
    }

    public function setAlternativeState(AlternativeArticleState $alternativeState): void
    {
        $this->alternativeState = $alternativeState;
    }
}

class AlternativeGraphTest extends TestCase
{
    private AlternativeArticle $article;
    private StateMachine       $stateMachine;

    protected function setUp(): void
    {
        $this->article      = new AlternativeArticle('Hi ! I\'m an article.');
        $this->stateMachine = new StateMachine;
    }

    public function test_it_has_transitions(): void
    {
        $this->assertCount(2, $this->article->getAlternativeState()::getTransitions());
        $this->assertCount(1, $this->stateMachine->getReachablesTransitions($this->article, AlternativeArticleState::class));

        $this->assertSame(
            AlternativeArticleState::READ,
            $this->stateMachine->getReachablesTransitions($this->article, AlternativeArticleState::class)[0]->getTargetState(),
        );
    }

    public function test_it_allows_to_transition(): void
    {
        $this->assertTrue($this->stateMachine->can($this->article, AlternativeArticleState::MARK_READ, AlternativeArticleState::class));
        $this->assertFalse($this->stateMachine->can($this->article, AlternativeArticleState::MARK_OLD, AlternativeArticleState::class));
    }

    public function test_it_applies_transition(): void
    {
        $this->stateMachine->apply($this->article, AlternativeArticleState::MARK_READ, AlternativeArticleState::class);

        $this->assertSame(AlternativeArticleState::READ, $this->article->getAlternativeState());
    }

    public function test_it_reject_bad_transition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->stateMachine->apply($this->article, AlternativeArticleState::MARK_OLD, AlternativeArticleState::class);
    }
}
