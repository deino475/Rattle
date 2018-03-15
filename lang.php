<?php
/**
Lexer/Paser/Interpreter for the Rattle Programming Language
Developed By Nile Dixon
Version 0.2

**/

############################################
#   Abstract Syntax Tree Nodes             #
#                                          #
#                                          #
############################################
class AST {}

class Compound extends AST {
	public $children = array();
	public function __construct($children = array()) {
		$this->children = $children;
	}
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

class ForEachStatement extends AST {

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

class FunctionDecl extends AST {
	public $name;
	public $parameters = array();
	public $then;

	public function __construct($name, $parameters, $then) {
		$this->name = $name;
		$this->parameters = $parameters;
		$this->then = $then;
	}
}

class StructDecl extends AST {
	public $name;
	public $parameters = array();

	public function __construct($name, $parameters) {
		$this->name = $name;
		$this->parameters = $parameters;
	}
} 

class FunctionDo extends AST {
	public $name;
	public $param_values = array();

	public function __construct($name, $param_values) {
		$this->name = $name;
		$this->param_values = $param_values;
	}
}

############################################
#   Data Type Nodes                        #
#                                          #
#                                          #
############################################

class BasicStruct {
	public $type;
	public $values = array();

	public function __construct($type, $values) {
		$this->type = $type;
		$this->values = $values;
	}
}

class FunctionObject {
	public $params;
	public $then;

	public function __construct($params, $then) {
		$this->params = $params;
		$this->then = $then;
	}
}

class ReturnObject {
	public $value;
	public function __construct($ret_val) {
		$this->value = $ret_val;
	}

	public function get_value() {
		return $this->value;
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
		'/^import/' => 'T_IMPORT',
		'/^none/' => 'T_NULL',
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
		'/^end function/' => 'T_END_FUNC',
		'/^end struct/' => 'T_END_STRUCT',
		'/^if/' => 'T_IF',
		'/^else/' => 'T_ELSE',
		'/^for/' => 'T_FOR',
		'/^while/' => 'T_WHILE',
		'/^unless/' => 'T_UNLESS',
		'/^until/' => 'T_UNTIL',	
		'/^then/' => 'T_THEN',
		'/^say/' => 'T_ECHO',
		'/^function/' => 'T_FUNC',
		'/^struct/' => 'T_STRUCT',
		'/^do/' => 'T_DO',
		'/^return/' => 'T_RETURN',
		'/^is greater than or equals/' => 'T_GREATER_EQUALS',
		'/^is less than or equals/' => 'T_LESS_EQUALS',
		'/^is greater than/' => 'T_GREATER',
		'/^is less than/' => 'T_LESS',
		'/^equals/' => 'T_EQUALS',
		'/^is/' => 'T_ASSIGN',
		'/^and/' => 'T_AND',
		'/^or/' => 'T_OR',
		'/^not/' => 'T_NOT',
		'/^list/' => 'T_LIST',
		'/^"(.*?)"/' => 'T_STRING',	
		'/^[+-]?([0-9]*[.])?[0-9]+/' => 'T_FLOAT',
		'/^[a-zA-Z][a-zA-Z0-9_]*/' => 'T_NAME',
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
		elseif ($this->current_token['token'] == 'T_FUNC') {
			$node = $this->function_statement();
		}
		elseif ($this->current_token['token'] == 'T_DO') {
			$node = $this->do_statement();
		}
		elseif ($this->current_token['token'] == 'T_STRUCT') {
			$node = $this->struct_decl();
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

	public function struct_decl() {
		$this->eat('T_STRUCT');
		$name = $this->variable();
		$params = array();
		while ($this->current_token['token'] != 'T_END_STRUCT') {
			array_push($params, $this->variable);
			while ($this->current_token['token'] == 'T_SEPARATE') {
				$this->eat('T_SEPARATE');
				array_push($params, $this->variable());
			}
		}
		$this->eat('T_END_STRUCT');
		$node = new StructDecl($name, $params);
		return $node;
	}

	public function function_statement() {
		$this->eat('T_FUNC');
		$name = $this->variable();
		$this->eat('T_LPAREN');
		$parameters = array();
		while ($this->current_token['token'] != 'T_RPAREN') {
			array_push($parameters, $this->variable());
			while ($this->current_token['token'] == 'T_SEPARATE') {
				$this->eat('T_SEPARATE');
				array_push($parameters, $this->variable());
			}
		}
		$this->eat('T_RPAREN');
		$then = array($this->statement());
		while ($this->current_token['token'] != 'T_END_FUNC') {
			while ($this->current_token['token'] == 'T_SEPARATE') {
				$this->eat('T_SEPARATE');
				array_push($then, $this->statement());
			}
		}
		$this->eat('T_END_FUNC');
		$compound = new Compound($then);
		$node = new FunctionDecl($name, $parameters, $compound);
		return $node;
	}

	public function do_statement() {
		$this->eat('T_DO');
		$function_name = $this->variable();
		$function_params = array();
		$this->eat('T_LPAREN');
		while ($this->current_token['token'] != 'T_RPAREN') {
			array_push($function_params, $this->expression());
			while ($this->current_token['token'] == 'T_SEPARATE') {
				array_push($function_params, $this->expression());
			}
		}
		$this->eat('T_RPAREN');
		$node = new FunctionDo($function_name, $function_params);
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
		if ($token['token'] == 'T_NOT') {
			$this->eat('T_NOT');
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

		if ($token['token'] == 'T_DO') {
			$node = $this->do_statement();
			return $node;
		}

		elseif ($token['token'] == 'T_LPAREN') {
			$this->eat('T_LPAREN');
			$result = $this->expression();
			$this->eat('T_RPAREN');
			return $result;
		}
		elseif ($token['token'] == 'T_DO') {
			$this->eat('T_DO');
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
		while (in_array($this->current_token['token'], array('T_PLUS','T_MINUS','T_CONCAT')) ){
			$operand = $this->current_token;
			switch ($operand['token']) {
				case 'T_PLUS':
					$this->eat('T_PLUS');
					break;
				case 'T_MINUS':
					$this->eat('T_MINUS');
					break;
				case 'T_CONCAT':
					$this->eat('T_CONCAT');
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
	public $current_stack = 0;
	public $var_space = array(array());
	public $function_space = array();
	public $struct_space = array();

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
		elseif ($node instanceof FunctionDecl) {
			return $this->visit_function_decl($node);
		}
		elseif ($node instanceof StructDecl) {
			return $this->visit_struct_decl($node);
		}
		elseif ($node instanceof FunctionDo) {
			return $this->visit_function_do($node);
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
			return $this->visit($node->left) . $this->visit($node->right);
		}
		return null;
	}

	public function visit_unaryop($node) {
		if ($node->operation == 'T_PLUS') {
			return + $this->visit($node->expression);
		}
		elseif ($node->operation == 'T_MINUS') {
			return - $this->visit($node->expression);
		}
		elseif ($node->operation == 'T_NOT') {
			return !$this->visit($node->expression);
		}
		return null;
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
		return null;
	}

	public function visit_cond($node) {
		$result = null;
		if ($this->visit($node->relation) == True) {
			foreach ($node->then as $statement) {
				$result = $this->visit($statement);
				if ($result != null) {
					return $result;
				}
			}
		}
		else {
			foreach ($node->else as $statement) {
				$result = $this->visit($statement);
				if ($result != null) {
					return $result;
				}
			}
		}
		return $result;
	}

	public function visit_unless($node) {
		$result = null;
		if ($this->visit($node->relation) == False) {
			foreach ($node->then as $statement) {
				$result = $this->visit($statement);
				if ($result != null) {
					return $result;
				}
			}
		}
		return $result;
	}

	public function visit_for($node) {
		$result = null;
		$this->visit($node->assignment);
		while ($this->visit($node->condition) == True) {
			foreach ($node->then as $statement) {
				$result = $this->visit($statement);
				if ($result != null) {
					return $result;
				}
			}
			$this->visit($node->operative_assignment);
		}
		return $result;
	}

	public function visit_while($node) {
		$result = null;
		while ($this->visit($node->relation) == True) {
			foreach ($node->then as $statement) {
				$result = $this->visit($statement);
				if ($result != null) {
					return $result;
				}
			}
		}
		return $result;
	}

	public function visit_until($node) {
		$result = null;
		while ($this->visit($node->relation) == False) {
			foreach ($node->then as $statement) {
				$result = $this->visit($statement);
				if ($result != null) {
					return $result;
				}
			}
		}
		return null;
	}

	public function visit_num($node) {
		return $node->value;
	}

	public function visit_compound($node) {
		$result = null;
		foreach ($node->children as $child) {
			$result = $this->visit($child);
			if ($result != null && $result instanceof ReturnObject) {
				return $result->get_value();
			}
		}
		return $result;
	}

	public function visit_noop($node) {}

	public function visit_assign($node) {
		$var_name = $node->left->value;
		$this->var_space[$this->current_stack][$var_name] = $this->visit($node->right);
		return null;
	}

	public function visit_function_decl($node) {
		$function_name = $node->name;
		$function_params = $node->parameters;
		$function_do = $node->then;
		$this->function_space[$function_name->value] = new FunctionObject($function_params,$function_do);
		return null;
	}

	public function visit_struct_decl($node) {
		$struct_name = $node->name;
		$params = $node->parameters;
		$this->struct_space[$struct_name] = $params;
		return null;
	}

	public function visit_make_struct($node) {
		$struct_name = $node->name;
		$struct_type = $node->type;

	}

	public function visit_function_do($node) {
		$return_value = null;
		$function_info = $this->function_space[$node->name->value];
		array_push($this->var_space, array());
		$this->current_stack = $this->current_stack + 1;
		for ($i=0; $i < sizeof($function_info->params); $i++) { 
			$param = $function_info->params[$i];
			$value = $node->param_values[$i];
			$this->var_space[$this->current_stack][$param->token['match']] = $this->visit($value);
		}
		$return_value = $this->visit($function_info->then);
		$this->current_stack = $this->current_stack - 1;
		array_pop($this->var_space);
		return $return_value;
	}

	public function visit_return_statement($node) {
		$x = $this->visit($node->return_value);
		return new ReturnObject($x);
	}

	public function visit_variable($node) {
		for ($i = sizeof($this->var_space) - 1; $i >= 0 ; $i--) { 
			if (isset($this->var_space[$i][$node->value])) {
				return $this->var_space[$i][$node->value];
			}
		}
		throw new Exception("Name does not exist.", 1);
	}

	public function visit_say($node) {
		echo $this->visit($node->thing_to_say);
		return null;
	}

	public function visit_text($node) {
		return substr($node->value,1,-1);
	}

	public function visit_boolean($node) {
		if ($node->value = 'true') {
			return True;
		}
		return False;
	}

	public function visit_import($node) {

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
	function go_to_the_store()
		say "Hello world",
		if 1 is less than 2 then
			say "<br>Bring some eggs"
		end if
	end function;

	function squared(x)
		return x times x
	end function;

	do go_to_the_store();
	x is do squared(-10) plus do squared(11);
	say "<br>";
	say x;
');
