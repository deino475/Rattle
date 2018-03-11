<?php

############################################
#   Abstract Syntax Tree Nodes             #
#                                          #
#                                          #
############################################
class AST {}

class Compound extends AST {
	public $children = array();
}

class Assign extends AST {
	public $left;
	public $token;
	public $right;

	public function __construct($left, $token, $right) {
		$this->left = $left;
		$this->token = $token;
		$this->right = $right;
	}
}

class IfStatement extends AST {
	public $relation;
	public $then;
	public $else;

	public function __construct($relation, $then, $else) {
		$this->relation = $relation;
		$this->then = $then;
		$this->else = $else;
	}
}

class UnlessStatement extends AST {
	public $relation;
	public $then;

	public function __construct($relation, $then) {
		$this->relation = $relation;
		$this->then = $then;
	}
}

class WhileStatement extends AST {
	public $relation;
	public $then = array();

	public function __construct($relation, $then) {
		$this->relation = $relation;
		$this->then = $then;
	}
}

class UntilStatement extends AST {
	public $relation;
	public $then;

	public function __construct($relation, $then) {
		$this->relation = $relation;
		$this->then = $then;
	}
}

class ForStatement extends AST {
	public $assignment;
	public $condition;
	public $operative_assignment;
	public $then; 

	public function __construct($assignment, $condition, $operative_assignment, $then) {
		$this->assignment = $assignment;
		$this->condition = $condition;
		$this->operative_assignment = $operative_assignment;
		$this->then = $then;
	}
}

class ReturnStatement extends AST {
	public $return_value;
	public function __construct($return_value) {
		$this->return_value = $return_value;
	}
}


class SayNode extends AST {
	public $thing_to_say;
	public function __construct($thing_to_say) {
		$this->thing_to_say = $thing_to_say;
	}
}

class BinaryOp extends AST {
	public $left;
	public $token;
	public $right;
	public $op;

	public function __construct($left, $op, $right) {
		$this->left = $left;
		$this->op = $op;
		$this->right = $right;
	}
}


class UnaryOp extends AST {
	public $operation;
	public $expression; 

	public function __construct($operation, $expression) {
		$this->operation = $operation;
		$this->expression = $expression;
	}
}

class RelOp extends AST {
	public $operation;
	public $left;
	public $right;

	public function __construct($operation, $left, $right) {
		$this->operation = $operation;
		$this->left = $left;
		$this->right = $right;
	}
}

class NoOp extends AST {}

class Num extends AST {
	public $token;
	public $value;

	public function __construct($token) {
		$this->token = $token['token'];
		$this->value = $token['match'];
	}
}

class Text extends AST {
	public $token;
	public $value;

	public function __construct($token) {
		$this->token = $token['token'];
		$this->value = $token['match'];
	}
}

class Boolean extends AST {
	public $token;
	public $value;

	public function __construct($token) {
		$this->token = $token['token'];
		$this->value = $token['match'];
	}
}

class Variable extends AST {
	public $token;
	public $value;

	public function __construct($token) {
		$this->token = $token;
		$this->value = $token['match'];
	}
}

class ProcedureDecl extends AST {
	public $name;
	public $then;

	public function __construct($name, $then) {
		$this->name = $name;
		$this->then = $then;
	}
}

class  ProcedureDo extends AST {
	public $name;

	public function __construct($name) {
		$this->name = $name;
	}
} 

############################################
#   Lexer and Parser                       #
#                                          #
#                                          #
############################################
class Lexer {
	public $position = 0;
	public $current_token = null;
	public $tokens = array();
	protected $terminals = array(
		'/^\/\*[\s\S]*?\*\/|([^:]|^)\/\/.*$/' => 'T_COMMENT',

		'/^plus/' => 'T_PLUS',
		'/^[+]/' => 'T_PLUS',
		'/^minus/' => 'T_MINUS',
		'/^[-]/' => 'T_MINUS',
		'/^times/' => 'T_MULTIPLY',
		'/^[*]/' => 'T_MULTIPLY',
		'/^divided by/' => 'T_DIVIDE',
		'/^\//' => 'T_DIVIDE',
		'/^modulus/' => 'T_MODULUS',
		'/^[%]/' => 'T_MODULUS',
		'/^true/' => 'T_BOOL',
		'/^false/' => 'T_BOOL',
		'/^concat/' => 'T_CONCAT',

		'/^[(]/' => 'T_LPAREN',
		'/^[)]/' => 'T_RPAREN',
		'/^[,]/' => 'T_SEPARATE',	
		'/^;/' => 'T_END',
		
		'/^end if/' => 'T_END_IF',
		'/^end else/' => 'T_END_ELSE',
		'/^end for/' => 'T_END_FOR',
		'/^end while/' => 'T_END_WHILE',
		'/^end unless/' => 'T_END_UNLESS',
		'/^end until/' => 'T_END_UNTIL',
		'/^end procedure/' => 'T_END_PROC',

		'/^if/' => 'T_IF',
		'/^else/' => 'T_ELSE',
		'/^for/' => 'T_FOR',
		'/^while/' => 'T_WHILE',
		'/^unless/' => 'T_UNLESS',
		'/^until/' => 'T_UNTIL',	
		'/^then/' => 'T_THEN',
		'/^say/' => 'T_ECHO',
		'/^procedure/' => 'T_PROC',
		'/^do/' => 'T_DO',
		'/^return/' => 'T_RETURN',

		'/^greater or equals/' => 'T_GREATER_EQUALS',
		'/^lesser or equals/' => 'T_LESS_EQUALS',
		'/^greater/' => 'T_GREATER',
		'/^lesser/' => 'T_LESS',
		'/^equals/' => 'T_EQUALS',

		'/^is/' => 'T_ASSIGN',
		'/^and/' => 'T_AND',
		'/^or/' => 'T_OR',

		'/^"(.*?)"/' => 'T_STRING',
		
		'/^[+-]?([0-9]*[.])?[0-9]+/' => 'T_FLOAT',
		'/^[a-zA-Z][a-zA-Z0-9]*/' => 'T_NAME',
		'/^\s+/' => 'T_WHITESPACE',
		'/^\n/' => 'T_NEWLINE'
	);

	public function run($source_code) {
		$tokens = array();
		$offset = 0;
		while ($offset < strlen($source_code)) {
			$result = $this->match($source_code, $offset);
			if (isset($result)) {
				if (!in_array($result['token'], array('T_WHITESPACE','T_NEWLINE','T_COMMENT'))){
					array_push($tokens, $result);
				}
				$offset += strlen($result['match']);
			}	
		}
		$this->tokens = $tokens;
		return $this->program();
	}

	public function match($line, $offset) {
		$current_str = substr($line, $offset);
		foreach ($this->terminals as $pattern => $token) {
			if (preg_match($pattern, $current_str, $matches)) {
				return array(
					'match' => $matches[0],
					'token' => $token
				);
			}
		}
		return null;
	}

	public function advance() {
		if ($this->position >= sizeof($this->tokens)) {
			$this->current_token = null;
		}
		else {
			$this->position += 1;
			if (isset($this->tokens[$this->position])) {
				$this->current_token = $this->tokens[$this->position];
			}	
		}
	}

	public function program() {
		$this->current_token = $this->tokens[$this->position];
		$node = $this->compound_statement();
		return $node;
	}

	public function compound_statement() {
		$nodes = $this->statement_lists();
		$root = new Compound;
		foreach ($nodes as $node) {
			array_push($root->children, $node);
		}
		return $root;
	}

	public function statement_lists() {
		$node = $this->statement();
		$results = array();
		array_push($results, $node);

		while ($this->current_token['token'] == 'T_END') {
			$this->eat('T_END');
			array_push($results, $this->statement());
		}

		if ($this->current_token['token'] == 'T_NAME') {
			throw new Exception("Why is an ID here?", 1);
		}
		return $results;
	}

	public function relation_op() {
		$part_one = $this->expression();
		if ($this->current_token['token'] == 'T_EQUALS') {
			$operation = 'T_EQUALS';
			$this->eat('T_EQUALS');
		}
		elseif ($this->current_token['token'] == 'T_NOT_EQUALS') {
			$operation = 'T_NOT_EQUALS';
			$this->eat('T_NOT_EQUALS');
		}
		elseif ($this->current_token['token'] == 'T_GREATER') {
			$operation = 'T_GREATER';
			$this->eat('T_GREATER');
		}
		elseif ($this->current_token['token'] == 'T_GREATER_EQUALS') {
			$operation = 'T_GREATER_EQUALS';
			$this->eat('T_GREATER_EQUALS');
		}
		elseif ($this->current_token['token'] == 'T_LESS') {
			$operation = 'T_LESS';
			$this->eat('T_LESS');
		}
		elseif ($this->current_token['token'] == 'T_LESS_EQUALS') {
			$operation = 'T_LESS_EQUALS';
			$this->eat('T_LESS_EQUALS');
		}
		$part_two = $this->expression();
		$relop_val = new RelOp($operation, $part_one, $part_two);
		return $relop_val;
	}

	public function statement() {
		if ($this->current_token['token'] == 'T_NAME') {
			$node = $this->assignment();
		}
		elseif ($this->current_token['token'] == 'T_IF') {
			$node = $this->if_statement();
		}
		elseif ($this->current_token['token'] == 'T_WHILE') {
			$node = $this->while_statement();
		}
		elseif ($this->current_token['token'] == 'T_FOR') {
			$node = $this->for_statement();
		}
		elseif ($this->current_token['token'] == 'T_UNLESS') {
			$node = $this->unless_statement();
		}
		elseif ($this->current_token['token'] == 'T_UNTIL') {
			$node = $this->until_statement();
		}
		elseif ($this->current_token['token'] == 'T_ECHO') {
			$node = $this->say();
		}
		elseif ($this->current_token['token'] == 'T_PROC') {
			$node = $this->procedure();
		}
		elseif ($this->current_token['token'] == 'T_DO') {
			$node = $this->do_statement();
		}
		elseif ($this->current_token['token'] == 'T_RETURN') {
			$node = $this->return_statement();
		}
		else {
			$node = $this->empty();
		}
		return $node;
	}

	public function assignment() {
		$left = $this->variable();
		$token = $this->current_token['token'];
		$this->eat('T_ASSIGN');
		$right = $this->expression();
		$node = new Assign($left, $token, $right);
		return $node;
	}

	public function procedure() {
		$this->eat('T_PROC');
		$name = $this->variable();
		$then = array($this->statement());
		while ($this->current_token['token'] != 'T_END_PROC') {
			while ($this->current_token['token'] == 'T_SEPARATE') {
				$this->eat('T_SEPARATE');
				array_push($then, $this->statement());
			}
		}
		$this->eat('T_END_PROC');
		$node = new ProcedureDecl($name, $then);
		return $node;
	}

	public function do_statement() {
		$this->eat('T_DO');
		$procedure_name = $this->variable();
		$node = new ProcedureDo($procedure_name);
		return $node;
	}

	public function if_statement() {
		$this->eat('T_IF');
		$relop_val = $this->relation_op();
		$this->eat('T_THEN');
		$then = array($this->statement());
		while ($this->current_token['token'] != 'T_END_IF') {
			while ($this->current_token['token'] == 'T_SEPARATE') {
				$this->eat('T_SEPARATE');
				array_push($then, $this->statement());
			}
		}
		$this->eat('T_END_IF');
		$else = array();
		if ($this->current_token['token'] == 'T_ELSE') {
			$this->eat('T_ELSE');
			while ($this->current_token['token'] != 'T_END_ELSE') {
				array_push($else, $this->statement());
				while ($this->current_token['token'] == 'T_SEPARATE') {
					$this->eat('T_SEPARATE');
					array_push($else, $this->statement());
				}
			}
			$this->eat('T_END_ELSE');
		}
		$node = new IfStatement($relop_val, $then, $else);
		return $node;
	}

	public function unless_statement() {
		$this->eat('T_UNLESS');
		$relop_val = $this->relation_op();
		$then = array($this->statement());
		while ($this->current_token['token'] != 'T_END_UNLESS') {
			while ($this->current_token['token'] == 'T_SEPARATE') {
				$this->eat('T_SEPARATE');
				array_push($then, $this->statement());
			}
		}
		$this->eat('T_END_UNLESS');
		$node = new UnlessStatement($relop_val, $then);
		return $node;
	}

	public function for_statement() {
		$this->eat('T_FOR');
		$assignment = $this->assignment();
		$this->eat('T_SEPARATE');
		$relop_val = $this->relation_op();
		
		$this->eat('T_SEPARATE');
		$operative_assignment = $this->assignment();
		$then = array($this->statement());
		while ($this->current_token['token'] != 'T_END_FOR') {
			while ($this->current_token['token'] == 'T_SEPARATE') {
				$this->eat('T_SEPARATE');
				array_push($then, $this->statement());
			}
		}
		$this->eat('T_END_FOR');		
		$node = new ForStatement($assignment, $relop_val, $operative_assignment, $then);
		return $node;
	}

	public function while_statement() {
		$this->eat('T_WHILE');
		$relop_val = $this->relation_op();
		$then = array();
		while ($this->current_token['token'] != 'T_END_WHILE') {
			array_push($then, $this->statement());
			while ($this->current_token['token'] == 'T_SEPARATE') {
				$this->eat('T_SEPARATE');
				array_push($then, $this->statement());
			}
		}
		$this->eat('T_END_WHILE');
		$node = new WhileStatement($relop_val, $then);
		return $node;
	}

	public function until_statement() {
		$this->eat('T_UNTIL');
		$relop_val = $this->relation_op();
		$then = array($this->statement());
		while ($this->current_token['token'] != 'T_END_UNTIL') {
			while ($this->current_token['token'] == 'T_SEPARATE') {
				$this->eat('T_SEPARATE');
				array_push($then, $this->statement());
			}
		}
		$this->eat('T_END_UNTIL');		
		$node = new UntilStatement($relop_val, $then);
		return $node;
	}

	public function return_statement() {
		$this->eat('T_RETURN');
		$part_one = $this->expression();
		if ($this->current_token['token'] == 'T_EQUALS') {
			$operation = 'T_EQUALS';
			$this->eat('T_EQUALS');
		}
		elseif ($this->current_token['token'] == 'T_NOT_EQUALS') {
			$operation = 'T_NOT_EQUALS';
			$this->eat('T_NOT_EQUALS');
		}
		elseif ($this->current_token['token'] == 'T_GREATER') {
			$operation = 'T_GREATER';
			$this->eat('T_GREATER');
		}
		elseif ($this->current_token['token'] == 'T_GREATER_EQUALS') {
			$operation = 'T_GREATER_EQUALS';
			$this->eat('T_GREATER_EQUALS');
		}
		elseif ($this->current_token['token'] == 'T_LESS') {
			$operation = 'T_LESS';
			$this->eat('T_LESS');
		}
		elseif ($this->current_token['token'] == 'T_LESS_EQUALS') {
			$operation = 'T_LESS_EQUALS';
			$this->eat('T_LESS_EQUALS');
		}
		else {
			$node = new ReturnStatement($part_one);
			return $node;
		}
		$part_two = $this->expression();
		$relop_val = new RelOp($operation, $part_one, $part_two);
		$node = new ReturnStatement($relop_val);
		return $node;
	}

	public function say() {
		$this->eat('T_ECHO');
		$thing_to_say = $this->expression();
		$node = new SayNode($thing_to_say);
		return $node;
	}

	public function variable() {
		$node = new Variable($this->current_token);
		$this->eat('T_NAME');
		return $node;
	}

	public function empty() {
		return new NoOp;
	}

	public function factor() {
		$token = $this->current_token;
		if ($token['token'] == 'T_PLUS') {
			$this->eat('T_PLUS');
			return new UnaryOp($token['token'], $this->factor());
		}
		if ($token['token'] == 'T_MINUS') {
			$this->eat('T_MINUS');
			return new UnaryOp($token['token'], $this->factor());
		}
		if ($token['token'] == 'T_CONCAT') {
			$this->eat('T_CONCAT');
			return new UnaryOp($token['token'], $this->factor());
		}
		if ($token['token'] == 'T_FLOAT') {
			$this->eat('T_FLOAT');
			return new Num($token);
		}
		if ($token['token'] == 'T_STRING') {
			$this->eat('T_STRING');
			return new Text($token);
		}

		if ($token['token'] == 'T_BOOL') {
			$this->eat('T_BOOL');
			return new Boolean($token);
		}

		elseif ($token['token'] == 'T_LPAREN') {
			$this->eat('T_LPAREN');
			$result = $this->expression();
			$this->eat('T_RPAREN');
			return $result;
		}
		else {
			$node = $this->variable();
			return $node;
		}		
	}

	public function term() {
		$result = $this->factor();
		while (in_array($this->current_token['token'], array('T_MULTIPLY','T_DIVIDE','T_MODULUS'))) {
			$token = $this->current_token;
			if ($token['token'] == 'T_MULTIPLY') {
				$this->eat('T_MULTIPLY');
			}
			elseif ($token['token'] == 'T_DIVIDE') {
				$this->eat('T_DIVIDE');
			}
			elseif ($token['token'] == 'T_MODULUS') {
				$this->eat('T_MODULUS');
			}
			$result = new BinaryOp($result, $token, $this->factor());
		}
		return $result;
	}

	public function expression() {
		$result = $this->term();
		while (in_array($this->current_token['token'], array('T_PLUS','T_MINUS')) ){
			$operand = $this->current_token;
			switch ($operand['token']) {
				case 'T_PLUS':
					$this->eat('T_PLUS');
					break;
				case 'T_MINUS':
					$this->eat('T_MINUS');
					break;
				default:
					break;
			}
			$result = new BinaryOp($result, $operand, $this->term());
		}
		return $result;
	}

	public function eat($token_type) {
		if ($this->current_token['token'] == $token_type) {
			$this->advance();
		}
		else {
			echo "Couldn't find $token_type";
		}
	}
}

############################################
#   Interpreter                            #
#                                          #
#                                          #
############################################

class Interpreter {
	public $lexer;
	public $global_space = array();
	public $procedure_space = array();

	public function __construct() {
		$this->lexer = new Lexer;
	}

	public function visit($node) {
		if ($node instanceof Compound) {
			return $this->visit_compound($node);
		}
		elseif ($node instanceof Assign) {
			return $this->visit_assign($node);
		}
		elseif ($node instanceof BinaryOp) {
			return $this->visit_binaryop($node);
		}
		elseif ($node instanceof UnaryOp) {
			return $this->visit_unaryop($node);
		}
		elseif ($node instanceof RelOp) {
			return $this->visit_relop($node);
		}
		elseif ($node instanceof Num) {
			return $this->visit_num($node);
		}
		elseif ($node instanceof Variable) {
			return $this->visit_variable($node);
		}
		elseif ($node instanceof IfStatement) {
			return $this->visit_cond($node);
		}
		elseif ($node instanceof UnlessStatement) {
			return $this->visit_unless($node);
		}
		elseif ($node instanceof WhileStatement) {
			return $this->visit_while($node);
		}
		elseif ($node instanceof UntilStatement) {
			return $this->visit_until($node);
		}
		elseif ($node instanceof ForStatement) {
			return $this->visit_for($node);
		}
		elseif ($node instanceof SayNode) {
			return $this->visit_say($node);
		}
		elseif ($node instanceof Text) {
			return $this->visit_text($node);
		}
		elseif ($node instanceof Boolean) {
			return $this->visit_boolean($node);
		}
		elseif ($node instanceof ProcedureDecl) {
			return $this->visit_procedure_decl($node);
		}
		elseif ($node instanceof ProcedureDo) {
			return $this->visit_procedure_do($node);
		}
		elseif ($node instanceof ReturnStatement) {
			return $this->visit_return_statement($node);
		}
		elseif ($node instanceof NoOp) {

		}
	}

	public function visit_binaryop($node) {
		if ($node->op['token'] == 'T_PLUS') {
			return $this->visit($node->left) + $this->visit($node->right);
		}
		elseif ($node->op['token'] == 'T_MINUS') {
			return $this->visit($node->left) - $this->visit($node->right);
		}
		elseif ($node->op['token'] == 'T_MULTIPLY') {
			return $this->visit($node->left) * $this->visit($node->right);
		}
		elseif ($node->op['token'] == 'T_DIVIDE') {
			return $this->visit($node->left) / $this->visit($node->right);
		}
		elseif ($node->op['token'] == 'T_MODULUS') {
			return $this->visit($node->left) % $this->visit($node->right);
		}
		elseif ($node->op['token'] == 'T_CONCAT') {
			#return $this->visit($node->left) .= $this->visit($node->right);
		}
	}

	public function visit_unaryop($node) {
		if ($node->operation == 'T_PLUS') {
			return + $this->visit($node->expression);
		}
		elseif ($node->operation == 'T_MINUS') {
			return - $this->visit($node->expression);
		}
	}

	public function visit_relop($node) {
		switch ($node->operation) {
			case 'T_EQUALS':
				return $this->visit($node->left) == $this->visit($node->right);
				break;
			case 'T_NOT_EQUALS':
				return $this->visit($node->left) != $this->visit($node->right);
				break;
			case 'T_GREATER':
				return $this->visit($node->left) > $this->visit($node->right);
				break;
			case 'T_LESS':
				return $this->visit($node->left) < $this->visit($node->right);
				break;
			case 'T_GREATER_EQUALS':
				return $this->visit($node->left) >= $this->visit($node->right);
				break;
			case 'T_LESS_EQUALS':
				return $this->visit($node->left) <= $this->visit($node->right);
				break;
			default:
				break;
		}
	}

	public function visit_cond($node) {
		if ($this->visit($node->relation) == True) {
			foreach ($node->then as $statement) {
				$this->visit($statement);
			}
		}
		else {
			foreach ($node->else as $statement) {
				$this->visit($statement);
			}
		}
	}

	public function visit_unless($node) {
		if ($this->visit($node->relation) == False) {
			foreach ($node->then as $statement) {
				$this->visit($statement);
			}
		}
	}

	public function visit_for($node) {
		$this->visit($node->assignment);
		while ($this->visit($node->condition) == True) {
			foreach ($node->then as $statement) {
				$this->visit($statement);
			}
			$this->visit($node->operative_assignment);
		}
	}

	public function visit_while($node) {
		while ($this->visit($node->relation) == True) {
			foreach ($node->then as $statement) {
				$this->visit($statement);
			}
		}
	}

	public function visit_until($node) {
		while ($this->visit($node->relation) == False) {
			foreach ($node->then as $statement) {
				$this->visit($statement);
			}
		}
	}

	public function visit_num($node) {
		return $node->value;
	}

	public function visit_compound($node) {
		foreach ($node->children as $child) {
			$this->visit($child);
		}
	}

	public function visit_noop($node) {}

	public function visit_assign($node) {
		$var_name = $node->left->value;
		$this->global_space[$var_name] = $this->visit($node->right);
	}
	
	public function visit_procedure_decl($node) {
		$procedure_name = $node->name;
		$procedure = $node->then;
		$this->procedure_space[$procedure_name->value] = $procedure;
	}

	public function visit_procedure_do($node) {
		$procedures = $this->procedure_space[$node->name->value];
		foreach ($procedures as $procedure) {
			$this->visit($procedure);
		}
		return null;
	}

	public function visit_return_statement($node) {
		return $this->visit($node->return_value);
	}

	public function visit_variable($node) {
		if (isset($this->global_space[$node->value])) {
			return $this->global_space[$node->value];
		}
		throw new Exception("Name does not exist.", 1);
		
	}

	public function visit_say($node) {
		echo $this->visit($node->thing_to_say);
	}

	public function visit_text($node) {
		return $node->value;
	}

	public function visit_boolean($node) {
		if ($node->value = 'true') {
			return True;
		}
		return False;
	}

	public function interpret($code) {
		$tree = $this->lexer->run($code);
		#print_r($tree);
		$result = $this->visit($tree);
	}
}
$interpreter = new Interpreter;
$interpreter->interpret('
	/** This is a comment. **/	
	x is 10;
	/** This is another comment.
	That transcends two lines. **/
	say x;
');
