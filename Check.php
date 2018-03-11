<?php
	namespace app;

	class Check
        {
                private $name;
                private $callback;

                public function __construct(string $name, callable $callback)
                {
                        $this->name = $name;
                        $this->callback = $callback;
                }

                public function getName(): string
                {
                        return $this->name;
                }

                public function call()
                {
                        $c = $this->callback;

                        return $c();
                }
        }

