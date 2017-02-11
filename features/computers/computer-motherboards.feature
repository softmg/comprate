Feature: Motherboards

  Background:
    Given the following motherboards exist:
      | id | manufacturer | cpu_manufacturer | socket  | memory_type |
      | 1  | ASUS         | Intel            | LGA1150 | DDR3 DIMM   |
      | 2  | ECS          | AMD              | S370    | DDR3 DIMM   |


  Scenario: Get available motherboards
    When I send GET request to "/computers/motherboards" with following json:
    """
    {
      "page": 1,
      "pageSize": 2
    }
    """
    Then I should get following json response:
    """
    [
      {
        "manufacturer": "ASUS",
        "cpu_manufacturer": "Intel",
        "socket": "LGA1150",
        "memory_type": "DDR3 DIMM"
      },
      {
        "manufacturer": "ECS",
        "cpu_manufacturer": "AMD",
        "socket": "S370",
        "memory_type": "DDR3 DIMM"
      }
    ]
    """
