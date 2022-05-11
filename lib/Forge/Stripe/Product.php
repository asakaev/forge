<?php declare(strict_types=1);

namespace Forge\Stripe;


final class Product {
  private string $id;
  private string $name;
  private int $amountCents;
  private int $quantity;
  private string $parentId;

  final function id(): string {
    return $this->id;
  }

  final function name(): string {
    return $this->name;
  }

  final function amountCents(): int {
    return $this->amountCents;
  }

  final function quantity(): int {
    return $this->quantity;
  }

  final function parentId(): string {
    return $this->parentId;
  }

  final function withName(string $name): Product {
    return Product::apply(
      $this->id,
      $name,
      $this->amountCents,
      $this->quantity,
      $this->parentId
    );
  }

  final function withAmountCents(int $amountCents): Product {
    return Product::apply(
      $this->id,
      $this->name,
      $amountCents,
      $this->quantity,
      $this->parentId
    );
  }

  final function __construct(string $id, string $name, int $amountCents, int $quantity, string $parentId) {
    $this->id = $id;
    $this->name = $name;
    $this->amountCents = $amountCents;
    $this->quantity = $quantity;
    $this->parentId = $parentId;
  }

  final static function apply(
    string $id,
    string $name,
    int $amountCents,
    int $quantity,
    string $parentId
  ): Product {
    return new Product($id, $name, $amountCents, $quantity, $parentId);
  }
}
