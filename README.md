# compressor

A PHP compression library.

# Usage

Note that strings will not be serialized, allowing for pre-serializing the data.
This is useful when certain strings may benefit from using `json_encode`.
Just don't forget to apply the appropriate deserializer externally.